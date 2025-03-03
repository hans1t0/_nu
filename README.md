# Sistema de Gestión de Actividades NU3

Este sistema permite gestionar distintos servicios educativos, incluyendo actividades extraescolares, ludoteca, guardería matinal y escuela de verano.

## Configuración de la Base de Datos

El sistema utiliza diferentes bases de datos para cada servicio:

- `nu3_db`: Base de datos principal
- `ludoteca_db`: Gestión de ludoteca
- `matinera_db`: Gestión de guardería matinal
- `verano_db`: Gestión de escuela de verano
- `actividades_escolares`: Gestión de actividades extraescolares

## Solución a errores de conexión

Si encuentras el mensaje de error: `Unknown database 'extraescolares_db'`, es porque la función de conexión está intentando conectar a una base de datos incorrecta. La base de datos correcta se llama `actividades_escolares`.

Para solucionar este problema:

1. Verifica que el archivo `admin/includes/functions.php` tenga el nombre correcto de la base de datos en la función `conectarDB()`.
2. Si necesitas crear la base de datos, puedes utilizar el script SQL proporcionado en `admin/sql/create_database.sql`.

## Estructura del Proyecto

El sistema está organizado de la siguiente manera:

```
/Users/jancok/Sites/localhost/_nu3/
├── admin/
│   ├── includes/
│   │   ├── config.php
│   │   └── functions.php
│   ├── services/
│   │   ├── extraescolares/
│   │   │   ├── functions.php
│   │   │   ├── index.php
│   │   │   └── inscritos.php
│   │   ├── ludoteca/
│   │   ├── matinera/
│   │   └── verano/
│   ├── views/
│   │   ├── dashboard.php
│   │   └── ...
│   └── index.php
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
└── index.php
```

## Módulo de Actividades Extraescolares

Este módulo permite:

1. Ver listado de todas las actividades extraescolares
2. Gestionar inscripciones de alumnos
3. Crear, editar y eliminar actividades
4. Exportar listados de actividades e inscritos

### Tablas Principales

- `actividades`: Almacena la información de las actividades
- `inscripciones`: Registra las inscripciones de alumnos en actividades
- `alumnos`: Datos de los alumnos

## Contacto

Para cualquier duda o soporte técnico, contacta con el administrador del sistema.
