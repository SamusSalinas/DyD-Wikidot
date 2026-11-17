# DyD-Wikidot

## Funciones actuales

- La página de inicio ofrece una bienvenida y accesos rápidos a las secciones
  principales.
- Las listas de hechizos se consultan desde la tabla `hechizos`, se agrupan por
  nivel y permiten desplegar la escuela y la descripción disponibles.
- **Equipamiento y Objetos** se consulta desde `objetos_equipo`; cada ficha
  muestra los campos disponibles en la base de datos.
- Los objetos mágicos se consultan desde `objetos_magicos` y se agrupan por
  rareza. Solo los que tienen descripción disponible enlazan a su ficha; los
  demás se muestran sin enlace. La migración añade descripciones para 269 de
  los 912 registros, cubiertos por el SRD 5.1. Los demás conservan sus
  metadatos sin copiar texto de fuentes no incluidas en el SRD.
- Las listas de hechizos se consultan desde la base de datos. Las antiguas
  listas HTML por clase se retiraron para evitar mantener contenido duplicado.

Las descripciones del SRD se conservan en inglés y se distribuyen bajo CC BY
4.0. La atribución requerida es:

> This work includes material taken from the System Reference Document 5.1
> ("SRD 5.1") by Wizards of the Coast LLC and available at
> https://dnd.wizards.com/resources/systems-reference-document. The SRD 5.1 is
> licensed under the Creative Commons Attribution 4.0 International License
> available at https://creativecommons.org/licenses/by/4.0/legalcode.

[`database/catalogo_objetos.sql`](./database/catalogo_objetos.sql) crea y carga
las dos tablas de objetos. Después, importa
[`database/descripciones_objetos_magicos_srd.sql`](./database/descripciones_objetos_magicos_srd.sql)
para añadir las descripciones del SRD; no hace falta mantener un archivo HTML
por objeto.

## Guía paso a paso: ejecutar en localhost con la base de datos

### En esta computadora

La base `dnd_wikidot` ya está restaurada en el MariaDB local de XAMPP. Sigue
estos pasos cada vez que quieras abrir la web:

1. Abre el **XAMPP Control Panel**.
2. Pulsa **Start** en **Apache** y en **MySQL**. Ambos deben quedar iniciados.
   En esta instalación Apache escucha en el puerto `8080` y MySQL en el `3306`.
3. Abre [phpMyAdmin](http://localhost:8080/phpmyadmin/) y confirma que existe
   la base `dnd_wikidot`. Debe contener las siete tablas base: `clases`, `dotes`,
   `hechizos`, `personajes`, `razas`, `subclases` y `trasfondos`.
4. Si todavía no existen `objetos_equipo` y `objetos_magicos`, selecciona
   `dnd_wikidot` en phpMyAdmin, abre **Importar** y carga
   [`database/catalogo_objetos.sql`](./database/catalogo_objetos.sql). Esto crea
   las dos tablas y carga los catálogos; puedes volver a importarlo sin duplicar
   los registros.
5. Si todavía no se hizo, importa también
   [`database/descripciones_objetos_magicos_srd.sql`](./database/descripciones_objetos_magicos_srd.sql)
   en la misma base. Este paso añade las columnas y textos cubiertos por el SRD
   5.1 y se puede repetir.
6. Abre la web en [http://localhost:8080/Proyecto/](http://localhost:8080/Proyecto/).
   La hoja está en
   [http://localhost:8080/Proyecto/index.php?page=personajes/hoja](http://localhost:8080/Proyecto/index.php?page=personajes/hoja).
7. En la hoja, comprueba que los menús **Clase** y **Raza** tienen opciones
   como «Bárbaro» y «Humano». Esas opciones se consultan desde la base.
8. Para editar el estilo, modifica [estructura.css](./estructura.css) y
   recarga la página. Usa `Ctrl+F5` si el navegador conserva una copia anterior.

La configuración por defecto de [conexion.php](./includes/conexion.php) es:

| Opción | Valor local |
| --- | --- |
| Servidor | `127.0.0.1` |
| Base de datos | `dnd_wikidot` |
| Usuario | `root` |
| Contraseña | Vacía (valor predeterminado de XAMPP en esta computadora) |
| Puerto MySQL | `3306` |

La conexión usa PDO con `pdo_mysql` y codificación `utf8mb4`. Si cambias los
datos de acceso, configura `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` en el
entorno de Apache; no pongas contraseñas reales en archivos versionados.

### Preparar otra computadora

La base está en el servicio MariaDB local; **no está incluida en el repositorio
de la web**. El repositorio `Fabi198/dnd_wikidot` contiene archivos internos
`.frm` y `.ibd`, que no se importan desde phpMyAdmin como un `.sql`.

1. Instala XAMPP y copia o clona este proyecto en
   `C:\xampp\htdocs\Proyecto`.
2. Inicia Apache y MySQL desde XAMPP.
3. Desde la computadora donde ya existe la base, abre phpMyAdmin, selecciona
   `dnd_wikidot` y ve a **Exportar → Rápido → SQL → Continuar**. Guarda el
   `.sql` fuera de `htdocs` y envíalo a la otra computadora. No lo agregues al
   repositorio si contiene datos privados.
4. En la otra computadora, abre phpMyAdmin, crea una base llamada
   `dnd_wikidot` con cotejamiento `utf8mb4_unicode_ci`, selecciónala y abre la
   pestaña **Importar**.
5. Selecciona el `.sql` exportado y pulsa **Importar**.
6. Importa [`database/catalogo_objetos.sql`](./database/catalogo_objetos.sql)
   para crear y cargar los catálogos de objetos. Se puede volver a importar sin
   duplicar sus registros.
7. Importa
   [`database/descripciones_objetos_magicos_srd.sql`](./database/descripciones_objetos_magicos_srd.sql)
   para añadir las descripciones con licencia SRD 5.1.
8. Confirma que aparecen las siete tablas base y las dos tablas de objetos.
9. Si esa instalación de XAMPP usa usuario o contraseña distintos, configura
   las variables `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` para Apache.
   Los valores predeterminados solo corresponden a esta instalación local.
10. Abre `http://localhost/Proyecto/` (o agrega `:8080` si Apache usa el puerto
   `8080`) y verifica las opciones de Clase y Raza en la hoja.

### Si algo falla

- **No abre localhost:** revisa que Apache esté iniciado. Si XAMPP muestra otro
  puerto, úsalo en la URL; en esta computadora es `8080`.
- **Error de conexión en la hoja:** confirma que MySQL está iniciado, que existe
  `dnd_wikidot` y que las credenciales de Apache coinciden con las de MariaDB.
- **Error `could not find driver`:** habilita la extensión PHP `pdo_mysql` en
  `C:\xampp\php\php.ini` y reinicia Apache.
- **La hoja muestra un error de catálogos:** confirma en phpMyAdmin que las
  tablas `clases` y `razas` existen y tienen registros.

La hoja actualmente lee las clases y razas de la base, pero todavía no guarda
personajes. La tabla `personajes` restaurada está vacía.
