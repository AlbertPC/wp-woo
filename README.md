# Bitforma

Tienda de demostración en español con WordPress, WooCommerce y un tema hijo de bloques de Twenty Twenty-Five. El catálogo de evaluación se importa desde el ZIP facilitado en `@inbox/`.

## Arrancar

Para preparar un entorno nuevo con Docker y Docker Compose:

```bash
bash scripts/install.sh
```

La instalación genera las credenciales locales, arranca los servicios y configura las páginas y los ajustes de demostración. Para reanudar el Codespace:

```bash
docker compose up -d --wait
```

Abre el enlace de 8080 desde la pestaña **Puertos** del Codespace con visibilidad **Privado**. La administración está en `/wp-admin/` de esa misma URL. Desde un navegador que se ejecute en el propio entorno se puede usar `http://localhost:8080`.

Detén los servicios con `docker compose stop`. MariaDB y los archivos del núcleo persisten en volúmenes Docker. Los medios, traducciones y extensiones instaladas se guardan en el directorio local `wp-content/`. Git ignora los datos de ejecución y las credenciales.

## Catálogo de evaluación

Con WordPress en marcha, importa los productos, variaciones, categorías y fotos locales del ZIP:

```bash
bash scripts/import-catalog.sh
# También acepta la ruta de otra copia del mismo ZIP.
bash scripts/import-catalog.sh /ruta/privada/oscar-evaluacion.zip
```

El importador usa el identificador de origen de cada tienda para evitar duplicados al volver a ejecutarlo. Conserva el SKU de origen cuando es único y asigna un SKU local estable a los vacíos o duplicados. Los precios de origen se guardan sin IVA y se muestran con el IVA de demostración. El ZIP, los medios de ejecución y las credenciales quedan fuera de Git. El pago, el envío y los emails se configuran localmente para la demo.

## Administración

El usuario de la instalación predeterminada es `bitforma-admin`. Consulta la contraseña en `.env.admin` de tu terminal privada. Si quieres cambiarla, utiliza el perfil de WordPress en la administración. El archivo conserva la contraseña inicial, así que actualízalo o elimínalo de forma segura después de cambiarla.

Para elegir un usuario distinto en una instalación nueva, ejecuta `BITFORMA_ADMIN_LOGIN=mi-usuario bash scripts/install.sh`. Puedes especificar también `BITFORMA_ADMIN_EMAIL`. Los correos de la tienda de demostración se interceptan en el plugin de uso obligatorio Bitforma Demo.

## Desarrollo

- `wp-content/themes/wp-woo/` contiene plantillas, patrones Gutenberg y estilos del tema hijo. En WordPress, abre **Apariencia → Editor** para editar plantillas y partes. Las personalizaciones guardadas allí se almacenan en la base de datos y tienen prioridad sobre los archivos del tema.
- `wp-content/plugins/wp-woo-blocks/` contiene el plugin de bloques del repositorio. `wp-content/mu-plugins/bitforma-demo.php` intercepta los correos de la demo.
- `scripts/configure-store.php` configura la tienda una sola vez. Deja el IVA de demostración al 21 %, el envío de prueba en España y el contra reembolso mediante bloques oficiales de carrito y checkout.
- La imagen de portada es una ilustración generada. Las fichas usan los medios del ZIP autorizado para el Codespace privado. La tipografía Inter se distribuye con su licencia OFL.
- El saludo del widget se sintetizó con Piper y su [modelo sharvard en español](https://huggingface.co/rhasspy/piper-voices/blob/main/es/es_ES/sharvard/medium/MODEL_CARD), basado en el [corpus Spanish Harvard de la Universidad de Edimburgo](https://datashare.ed.ac.uk/handle/10283/574) ([CC BY 3.0](https://creativecommons.org/licenses/by/3.0/)).

Consulta el estado de la tienda desde la línea de comandos:

```bash
docker compose exec -T -u www-data wordpress wp plugin list
docker compose exec -T -u www-data wordpress wp theme list
docker compose exec -T -u www-data wordpress wp post list --post_type=product
docker compose exec -T -u www-data wordpress wp eval-file /opt/bitforma/scripts/verify-store.php
docker compose logs --tail=100 wordpress
docker compose exec -T wordpress sh -c 'test ! -f /tmp/bitforma-wordpress.log || tail -n 50 /tmp/bitforma-wordpress.log'
```
