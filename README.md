# Gym Control · AE4 / TIFAS

**Integrantes** — Santiago Tarnoski, Franco Olexyn y Santino Martins.

Gym Control permite consultar y comparar planes de gimnasio, elegir una membresía y consultar su vigencia. El administrador gestiona el catálogo. La aplicación reúne la portada, la tabla, las tarjetas, el detalle y el formulario de la AE1 en una SPA con JavaScript Vanilla. PHP controla las operaciones y PDO conecta con la base MySQL mediante el controlador `pdo_mysql`.

- [Índice de documentación y antecedentes](docs/README.md)
- [Análisis y diseño de la AE1](docs/antecedentes/ae1/Gym_Control_AE1_Analisis_y_Diseno.pdf)
- [Informe técnico final de la AE4](docs/Gym_Control_AE4_Informe_Tecnico.pdf)
- [Aportes realizados por el equipo](docs/Aportes_AE4.md)
- [Evidencias de Lighthouse y pruebas](docs/evidencias/README.md)

## Estructura

```text
gym-control-ae4/
├── public/                  Vista SPA
│   ├── index.html           Vistas semánticas y formularios
│   ├── css/styles.css       Maqueta responsive de AE1 con ajustes de accesibilidad
│   └── js/app.js            Navegación, fetch, estado de la interfaz y renderizado
├── api/                     Controladores REST y utilidades HTTP y de sesión
├── models/                  Acceso a datos mediante PDO
├── config/                  Conexión PDO y configuración local
├── database/                Esquema SQL y catálogo inicial
├── scripts/                 Alta de cuentas y pruebas automatizadas del contador
└── docs/                    Informe AE4, evidencias, antecedentes e informes individuales
```

Flujo MVC desacoplado: **vista HTML/CSS + JavaScript → fetch HTTP/JSON → controladores PHP → modelos PDO → MySQL**. La vista permanece en un documento HTML y cambia de sección mediante rutas con fragmentos. El navegador comparte origen con la API.

## Instalación en Windows con XAMPP

1. Extraé el ZIP o cloná el repositorio dentro de `C:\xampp\htdocs\gym-control-ae4`. Verificá que `public\index.html` y `database\gym_control.sql` estén dentro de esa carpeta.
2. Abrí el panel de XAMPP e iniciá **Apache** y **MySQL**. En XAMPP 8.2.12, el servicio rotulado MySQL ejecuta MariaDB 10.4.32. Se usó esa distribución para las pruebas.
3. Abrí [phpMyAdmin](http://localhost/phpmyadmin/), ingresá a **Importar**, elegí `database/gym_control.sql` y ejecutá la importación. Se crean `gym_control`, cuatro tablas y los cuatro planes de la AE1. Usá una base separada de cualquier instancia existente con datos reales.
4. Si configuraste otro usuario, contraseña o puerto, copiá `config/local.ejemplo.php` a `config/local.php` y ajustá los valores. El valor inicial coincide con XAMPP local (usuario `root`, puerto 3306, clave vacía). Para otra instalación, usá una cuenta de base con permisos limitados a las tablas del sistema.
5. Confirmá que `extension=pdo_mysql` y `extension=mbstring` estén activas en `C:\xampp\php\php.ini`. La configuración de Apache debe permitir los archivos `.htaccess` de `htdocs` y tener `mod_rewrite` y `mod_headers` habilitados. La SPA usa rutas con `#` y abre en [http://localhost/gym-control-ae4/public/](http://localhost/gym-control-ae4/public/). Si Apache usa otro puerto, incluilo en la URL.
6. Creá una cuenta de socio desde **Ingresar → Crear cuenta**. Para crear un administrador, abrí PowerShell y ejecutá estos comandos. El script solicita la clave por la entrada estándar.

   ```powershell
   Set-Location C:\xampp\htdocs\gym-control-ae4
   & C:\xampp\php\php.exe scripts/crear-usuario.php admin "Administrador de prueba" admin@gymcontrol.test
   ```

7. Ingresá como administrador y abrí **Administrar planes**. Para demostrar una baja, creá un plan de prueba y eliminalo antes de vincularlo a una membresía o consulta.

## Casos de uso y persistencia

| Antecedente | Recorrido en AE4 | Datos |
| --- | --- | --- |
| CU01 · Consultar planes | Portada → Ver planes | `planes` |
| CU02 · Visualizar listado | Tabla y tarjetas; búsqueda en tarjetas | `planes` |
| CU03 · Consultar detalle | Ficha dinámica del plan elegido | `planes` |
| CU04 · Seleccionar un plan | Ingreso, selección, confirmación e historial | `usuarios`, `planes`, `membresias` |
| RF05 · Gestión de planes | Alta, consulta, edición y baja con rol administrador | `planes` |
| AE2 · Tarjetas 3, 4 y 11 | Filtro, cuenta persistida y reclamos/consultas en la ficha | `planes`, `usuarios`, `consultas` |

Cada cantidad representa períodos consecutivos del mismo plan para la misma cuenta. La primera membresía empieza en la fecha local argentina y su fin se excluye de la vigencia. Si existe una vigente o programada, el nuevo período empieza cuando finaliza el último. El servidor calcula el total en pesos y aplica el 10 % para `UCP10`. El importe, el nombre del plan y el medio de pago se guardan con la membresía. El cobro se gestiona de forma presencial.

## API

Base local `http://localhost/gym-control-ae4/api/`. Éxito como objeto JSON, errores de aplicación con `{"error":"mensaje"}`. Las escrituras JSON usan `Content-Type: application/json`. Las escrituras autenticadas requieren la cookie de sesión y el encabezado `X-CSRF-Token` que devuelve `login.php`.

| Método | Ruta relativa | Acción y acceso |
| --- | --- | --- |
| GET | `planes.php` | Catálogo público |
| GET | `planes.php?id=N` | Detalle público |
| POST | `planes.php` | Alta, administrador |
| PUT | `planes.php?id=N` | Edición, administrador |
| DELETE | `planes.php?id=N` | Baja, administrador |
| POST | `usuarios.php` | Registro público con rol socio |
| GET | `login.php` | Recupera la sesión activa |
| POST | `login.php` | Verifica la clave y abre sesión |
| DELETE | `login.php` | Cierra la sesión activa |
| GET | `membresias.php` | Historial de la cuenta de socio autenticada |
| POST | `membresias.php` | Asigna o renueva una membresía propia |
| POST | `consultas.php` | Registra un reclamo o consulta de socio |

## Auditoría y demostración

Las evidencias de accesibilidad y los registros generales de pruebas corresponden a la línea base documentada el 15/11/2026, anterior a los aportes de sesión y contador del 16/11. No representan una nueva auditoría de la versión final. El [índice de evidencias](docs/evidencias/README.md) distingue los entornos, fechas y alcances de cada comprobación.

1. En Chrome, abrí la SPA y usá **Tab** para recorrer enlaces, controles y botones. Activá los controles con **Enter**. Escribí los datos de formulario cuando el campo tenga foco.
2. Abrí DevTools con `F12`, seleccioná **Lighthouse**, elegí **Accessibility** y ejecutá **Analyze page load**. Auditá también la vista de selección con una sesión de socio activa y el panel de administrador. Si corre en un perfil con extensiones, usá un perfil limpio.
3. En **Network → Fetch/XHR**, confirmá una membresía y revisá `POST membresias.php`, el HTTP 201 y el JSON. Volvé a **Mi cuenta** para comprobar el historial. Después de cerrar sesión, ingresar directo a `/gym-control-ae4/api/membresias.php` devuelve 401 con JSON.
4. Para respaldar el código, usá el ZIP de entrega. Para respaldar datos de una instalación que ya se utilizó, exportá la base por separado desde phpMyAdmin.

Para ejecutar las pruebas del contador con Node.js: `node scripts/pruebas-consultas.cjs`. Simulan los controles y las respuestas HTTP; no requieren XAMPP ni comprueban la persistencia real.

El repositorio de entrega es [santi-ms/gym-control-ae4](https://github.com/santi-ms/gym-control-ae4). El proyecto parte del [trabajo grupal original](https://github.com/santiagotarnoski/gym-control-ae4); esta copia registra su importación por componentes y los aportes posteriores. Los documentos AE1 y AE2 se conservan como antecedentes, con sus rutas y resultados históricos.
