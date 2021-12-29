<?php

return [
    'activate_account_notification_body' => 'Enviamos este correo porque recibimos una solicitud de restablecimiento de contraseña para tu cuenta.',
    'activate_account_notification_subject' => 'Activa tu cuenta',
    'addon_has_more_releases_beyond_license_body' => 'Puedes actualizar, pero deberás migrar tu licencia o comprar una nueva.',
    'addon_has_more_releases_beyond_license_heading' => 'Este complemento tiene más versiones más allá del límite de tu licencia.',
    'addon_list_loading_error' => 'Algo salió mal al cargar los complementos. Intenta nuevamente más tarde.',
    'asset_container_allow_uploads_instructions' => 'Cuando está habilitado, los usuarios pueden cargar archivos en este contenedor.',
    'asset_container_blueprint_instructions' => 'Los planos definen campos personalizados adicionales al editar medios.',
    'asset_container_create_folder_instructions' => 'Al activarse dará a los usuarios la capacidad de crear carpetas en este contenedor.',
    'asset_container_disk_instructions' => 'Los discos del sistema de archivos especifican dónde se alojan los medios&mdash;localmente o bien en una localización remota como Amazon S3. Pueden ser configurados en `config/filesystems.php`',
    'asset_container_handle_instructions' => 'Se usa para hacer referencia a este contenedor en la interfaz. Es complicado cambiarlo más tarde.',
    'asset_container_intro' => 'Los medios y documentos viven en carpetas en el servidor u otros servicios de almacenamiento de archivos. Cada una de estas ubicaciones se llama contenedor.',
    'asset_container_move_instructions' => 'Al activarse permitirá a los usuarios mover medios dentro del contenedor.',
    'asset_container_quick_download_instructions' => 'Al activarse añadirá un enlace de descarga rápida en la Biblioteca de Medios.',
    'asset_container_rename_instructions' => 'Al activarse permitirá a los usuarios renombrar medios en este contenedor.',
    'asset_container_title_instructions' => 'Generalmente un sustantivo plural, como "Imágenes" o "Documentos"',
    'asset_folders_directory_instructions' => 'Recomendamos evitar espacios y caracteres especiales para mantener limpas tus URLs.',
    'blueprints_intro' => 'Los planos definen y organizan campos para crear modelos de contenido para colecciones, formularios y otros tipos de datos.',
    'blueprints_hidden_instructions' => 'Oculta el plano de los botones de creación en el Panel de Control',
    'blueprints_title_instructions' => 'Por lo general, un sustantivo singular, como "Artículo" o "Producto.',
    'cache_utility_application_cache_description' => 'El caché unificado de Laravel utilizado por Statamic, complementos de terceros y paquetes de Composer.',
    'cache_utility_description' => 'Administra y revisa información importante sobre las diversas capas de caché de Statamic.',
    'cache_utility_image_cache_description' => 'El caché de imágenes almacena copias de todas las imágenes transformadas y redimensionadas.',
    'cache_utility_stache_description' => 'El Stache (pronunciado "stash") es el almacén de contenido de Statamic que funciona de manera muy similar a una base de datos. Se genera automáticamente a partir de tus archivos de contenido.',
    'cache_utility_static_cache_description' => 'Las páginas estáticas omiten Statamic por completo y se procesan directamente desde tu servidor, para obtener el máximo rendimiento.',
    'choose_entry_localization_deletion_behavior' => 'Elige la acción que deseas realizar en las entradas localizadas.',
    'collection_configure_date_behavior_private' => 'Privado - Oculta en listados, URLs 404',
    'collection_configure_date_behavior_public' => 'Público - Siempre visible',
    'collection_configure_date_behavior_unlisted' => 'Escondido - Oculta en listados, URLs visibles',
    'collection_configure_dated_instructions' => 'Las fechas de publicación se pueden usar para programar y caducar el contenido.',
    'collection_configure_handle_instructions' => 'Usado para referenciar a esta colección en el frontend. Es complicado cambiarlo más tarde.',
    'collection_configure_intro' => 'Una colección es un grupo de entradas relacionadas que comparten comportamiento, atributos y configuraciones.',
    'collection_configure_layout_instructions' => 'Establecer el diseño predeterminado de esta colección. Las entradas pueden anular esta configuración con un campo de tipo  `template` llamado `layout`. Es inusual cambiar esta configuración.',
    'collection_configure_template_instructions' => 'Establece la plantilla predeterminada de esta colección. Las entradas pueden anular esta configuración con un campo de tipo `template`.',
    'collection_configure_title_instructions' => 'Recomendamos un sustantivo plural, como "Artículos" o "Productos".',
    'collection_next_steps_configure_description' => 'Configura URLs y rutas; define planos, comportamientos de fechas, orden y otras opciones.',
    'collection_next_steps_create_entry_description' => 'Crea la primera entrada o agrega un puñado de entradas de muestra, depende de ti.',
    'collection_next_steps_documentation_description' => 'Obtén más información sobre las colecciones, cómo funcionan y cómo configurarlas.',
    'collection_next_steps_scaffold_description' => 'Genera rápidamente planos vacíos y vistas para el frontend según el nombre de la colección.',
    'collection_scaffold_instructions' => 'Elige qué recursos en blanco generar. Los archivos existentes no se sobrescribirán.',
    'collections_amp_instructions' => 'Habilitar Páginas Móviles Aceleradas (AMP). Agrega automáticamente rutas y URL para entradas en esta colección. Obtén más información en la [documentación](https://statamic.dev/amp)',
    'collections_blueprint_instructions' => 'Las entradas en esta colección pueden usar cualquiera de estos planos.',
    'collections_default_publish_state_instructions' => 'Mientras se crean nuevas entradas en esta colección el interruptor de publicación será **verdadero** en vez de **falso** (borrador).',
    'collections_future_date_behavior_instructions' => 'Cómo deben comportarse las entradas con fecha futura.',
    'collections_links_instructions' => 'Las entradas en esta colección pueden contener enlaces (redirecciones) a otras entradas o URLs.',
    'collections_mount_instructions' => 'Elige una entrada en la que se debe montar esta colección. Obtén más información en la [documentación](https://statamic.dev/collections-and-entries#mounting).',
    'collections_orderable_instructions' => 'Habilita el ordenamiento manual mediante arrastrar y soltar.',
    'collections_past_date_behavior_instructions' => 'Cómo deben comportarse las entradas con fecha pasada.',
    'collections_route_instructions' => 'La ruta controla el patrón del URL de las entradas. Obtén más información en la [documentación](https://statamic.dev/collections#meta-variables).',
    'collections_sort_direction_instructions' => 'La dirección de ordenado predeterminada.',
    'collections_taxonomies_instructions' => 'Conecta las entradas de esta colección a las taxonomías. Los campos se agregarán automáticamente a los formularios.',
    'email_utility_configuration_description' => 'La configuración del correo se configura en <code>:path</code>',
    'email_utility_description' => 'Verifica la configuración del correo electrónico y envía una prueba.',
    'expect_root_instructions' => 'Considerada la primera página en el árbol como "raíz" o página de "inicio".',
    'field_conditions_instructions' => 'Cuándo mostrar u ocultar este campo.',
    'field_desynced_from_origin' => 'Desincronizado del origen. Haz clic para sincronizar y restablecer el valor al de origen.',
    'field_synced_with_origin' => 'Sincronizado con el origen. Haz clic o edita el campo para desincronizar.',
    'field_validation_advanced_instructions' => 'Agrega una validación más avanzada a este campo.',
    'field_validation_required_instructions' => 'Controla si este campo es obligatorio o no.',
    'fields_blueprints_description' => 'Los planos definen los campos para estructuras de contenido como colecciones, taxonomías, usuarios y formularios.',
    'fields_display_instructions' => 'La etiqueta del campo mostrado en el Panel de Control.',
    'fields_fieldsets_description' => 'Los conjuntos de campos son agrupaciones de campos simples, flexibles y completamente opcionales que ayudan a organizar campos reutilizables y preconfigurados.',
    'fields_handle_instructions' => 'La variable de plantilla del campo.',
    'fields_instructions_instructions' => 'Se muestra debajo de la etiqueta del campo, como si fuera texto. Puedes usar Markdown.',
    'fields_listable_instructions' => 'Controla la visibilidad de la columna en este campo.',
    'fieldset_import_fieldset_instructions' => 'El conjunto de campos a importar.',
    'fieldset_import_prefix_instructions' => 'El prefijo que debe aplicarse a cada campo cuando se importa. ej: texto_',
    'fieldset_intro' => 'Los conjuntos de campos son un complemento opcional de los planos, ya que actúan como parciales reutilizables que se pueden usar dentro de los planos.',
    'fieldset_link_fields_prefix_instructions' => 'Todos los campos en el conjunto de campos vinculados tendrán el prefijo. Útil si deseas importar los mismos campos varias veces.',
    'fieldsets_handle_instructions' => 'Se utiliza para hacer referencia a este conjunto de campos en otro lugar. Es complicado cambiarlo más tarde.',
    'fieldsets_title_instructions' => 'Por lo general, describe qué campos estarán dentro, como "Bloque de imagen" o "Metadatos".',
    'focal_point_instructions' => 'Establecer un punto focal permite el recorte dinámico de imágenes con un objeto que permanezca siempre en cuadro.',
    'focal_point_previews_are_examples' => 'Las previsualizaciones de recortes son únicamente de ejemplo.',
    'forgot_password_enter_email' => 'Ingresa tu dirección de email y te enviaremos un enlace para restablecer tu contraseña.',
    'form_configure_blueprint_instructions' => 'Elige entre planos existentes o crea uno nuevo.',
    'form_configure_email_from_instructions' => 'Deja en blanco para volver al predeterminado del sitio',
    'form_configure_email_html_instructions' => 'La vista para la versión HTML de este correo.',
    'form_configure_email_instructions' => 'Configura los correos electrónicos que se enviarán cuando se reciban respuestas al formulario.',
    'form_configure_email_reply_to_instructions' => 'Déjalo en blanco para usar el remitente.',
    'form_configure_email_subject_instructions' => 'Línea de asunto del correo.',
    'form_configure_email_text_instructions' => 'La vista para la versión de texto de este correo.',
    'form_configure_email_to_instructions' => 'Dirección de correo electrónico del destinatario.',
    'form_configure_handle_instructions' => 'Se usa para hacer referencia a este formulario en la interfaz. Es complicado cambiarlo más tarde.',
    'form_configure_honeypot_instructions' => 'Nombre del campo para usar como honeypot. Los "honeypots" son campos especiales utilizados para reducir el spam de bots.',
    'form_configure_intro' => 'Los formularios se utilizan para recopilar información de los visitantes y enviar eventos y notificaciones cuando hay nuevas respuestas.',
    'form_configure_store_instructions' => 'Desactivar para dejar de almacenar envíos. Aún se enviarán eventos y notificaciones por correo electrónico.',
    'form_configure_title_instructions' => 'Por lo general, una llamada a la acción, como "Contáctanos".',
    'getting_started_widget_blueprints' => 'Los planos definen campos personalizados utilizados para crear y almacenar contenido.',
    'getting_started_widget_collections' => 'Las colecciones contienen los diferentes tipos de contenido en el sitio.',
    'getting_started_widget_docs' => 'Conoce Statamic, entendiendo sus capacidades de la manera correcta.',
    'getting_started_widget_header' => 'Empezando con Statamic 3',
    'getting_started_widget_intro' => 'Para comenzar a construir tu nuevo sitio en Statamic 3, te recomendamos iniciar con estos pasos.',
    'getting_started_widget_navigation' => 'Crea listas multinivel que pueden ser usadas para mostrar barras de navegación, menús, etcétera.',
    'getting_started_widget_pro' => 'Statamic Pro agrega cuentas de usuario ilimitadas, roles y permisos; además de integración de Git, revisiones, funcionalidad multisitio y más.',
    'git_disabled' => 'La integración de Statamic con Git se encuentra actualmente desactivada.',
    'git_nothing_to_commit' => 'Nada para hacer commit, ¡rutas de contenido limpias!',
    'git_utility_description' => 'Administra el contenido rastreado en Git.',
    'global_search_open_using_slash' => 'Puedes abrir la búsqueda global con la tecla <kbd>/</kbd>',
    'global_set_config_intro' => 'Los conjuntos globales administran el contenido disponible en todo el sitio; como los detalles de la compañía, la información de contacto o la configuración del front-end.',
    'global_set_no_fields_description' => 'Puedes agregar campos al plano o puedes manualmente agregar variables al conjunto en sí.',
    'globals_blueprint_instructions' => 'Controla los campos que se mostrarán al editar las variables.',
    'globals_configure_handle_instructions' => 'Se usa para hacer referencia a este conjunto global en la interfaz. Es complicado cambiarlo más tarde.',
    'globals_configure_intro' => 'Un conjunto global es un grupo de variables disponibles en todas las páginas del front-end.',
    'globals_configure_title_instructions' => 'Recomendamos un sustantivo que represente el contenido del conjunto, como "Marca" o "Empresa".',
    'licensing_config_cached_warning' => 'Cualquier cambio que realice en sus archivos .env o config no se detectará hasta que borre el caché. Si ve resultados inesperados de licencias aquí, puede deberse a esto. Puede usar el <code>php artisan config:cache</code> para regenerar el caché.',
    'licensing_error_invalid_domain' => 'Dominio inválido',
    'licensing_error_invalid_edition' => 'La licencia es para la edición edición :edition',
    'licensing_error_no_domains' => 'No hay dominios definidos',
    'licensing_error_no_site_key' => 'Sin clave de licencia del sitio',
    'licensing_error_outside_license_range' => 'Licencia válida para versiones :start a :end',
    'licensing_error_unknown_site' => 'Sitio desconocido',
    'licensing_error_unlicensed' => 'No licenciado',
    'licensing_production_alert' => 'Este sitio utiliza Statamic Pro y complementos comerciales. Adquiera las licencias adecuadas.',
    'licensing_production_alert_addons' => 'Este sitio utiliza complementos comerciales. Adquiera las licencias adecuadas.',
    'licensing_production_alert_statamic' => 'Este sitio utiliza Statamic Pro. Adquiera una licencia.',
    'licensing_sync_instructions' => 'Los datos de statamic.com se sincronizan una vez por hora. Forza una sincronización para ver los cambios que hayas realizado.',
    'licensing_trial_mode_alert' => 'Este sitio utiliza Statamic Pro y complementos comerciales. Asegúrese de comprar licencias antes del lanzamiento. ¡Gracias!',
    'licensing_trial_mode_alert_addons' => 'Este sitio utiliza complementos comerciales. Asegúrese de comprar licencias antes del lanzamiento. ¡Gracias!',
    'licensing_trial_mode_alert_statamic' => 'Este sitio utiliza Statamic Pro. Asegúrese de comprar una licencia antes de iniciar. ¡Gracias!',
    'licensing_utility_description' => 'Ver y resolver los detalles de la licencia.',
    'max_depth_instructions' => 'Establece el número máximo de niveles en los que una página pueda ser anidada. Déjalo en blanco para que sea ilimitado.',
    'max_items_instructions' => 'Establece un número máximo de elementos seleccionables.',
    'navigation_configure_blueprint_instructions' => 'Elige entre planos existentes o crea uno nuevo.',
    'navigation_configure_collections_instructions' => 'Habilita el enlace a entradas en estas colecciones.',
    'navigation_configure_handle_instructions' => 'Se usa para hacer referencia a esta navegación en la interfaz. Es complicado cambiarlo más tarde.',
    'navigation_configure_intro' => 'Las navegaciones son listas de enlaces de varios niveles que se pueden utilizar para crear barras de navegación, menús, mapas del sitio y cualquier forma de navegación en el front-end.',
    'navigation_configure_settings_intro' => 'Habilita la vinculación a colecciones, establece una profundidad máxima y otros comportamientos.',
    'navigation_configure_title_instructions' => 'Recomendamos un nombre que coincida con el lugar donde se utilizará, como "Navegación principal", "Navegación de cuenta" o "Navegación de pie de página".',
    'navigation_documentation_instructions' => 'Obtén más información sobre la creación, configuración y visualización de Navegaciones.',
    'navigation_link_to_entry_instructions' => 'Agregar un enlace a una entrada. Habilita el enlace a colecciones adicionales en el área de Configuración.',
    'navigation_link_to_url_instructions' => 'Agrega un enlace a cualquier URL interna o externa. Habilita el enlace a las entradas en el área de Configuración.',
    'outpost_error_422' => 'Error al comunicarse con statamic.com.',
    'outpost_error_429' => 'Demasiadas solicitudes a statamic.com.',
    'outpost_issue_try_later' => 'Hubo un problema al comunicarse con statamic.com. Por favor, inténtalo de nuevo más tarde.',
    'phpinfo_utility_description' => 'Verifica la configuración de PHP y los módulos instalados.',
    'publish_actions_create_revision' => 'Se creará una revisión basada en la copia de trabajo. La revisión actual no cambiará.',
    'publish_actions_current_becomes_draft_because_scheduled' => 'Dado que la revisión actual es la publicada y has seleccionado una fecha en el futuro, al enviar la revisión ésta actuará como un borrador hasta la fecha seleccionada.',
    'publish_actions_publish' => 'Los cambios en la copia de trabajo se aplicarán a la entrada y se publicarán de inmediato.',
    'publish_actions_schedule' => 'Los cambios en la copia de trabajo se aplicarán a la entrada y aparecerá publicada en la fecha seleccionada.',
    'publish_actions_unpublish' => 'La revisión actual se ocultará.',
    'reset_password_notification_body' => 'Te enviamos este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para tu cuenta.',
    'reset_password_notification_no_action' => 'Si no solicitaste un restablecimiento de contraseña, tienes que hacer nada más.',
    'reset_password_notification_subject' => 'Notificación de restablecimiento de contraseña',
    'role_change_handle_warning' => 'Cambiar el identificador no actualizará las referencias a éste en usuarios y grupos.',
    'role_handle_instructions' => 'Los identificadores se utilizan para hacer referencia a este rol en la interfaz. No se puede cambiar fácilmente.',
    'role_intro' => 'Los roles son grupos de permisos de acceso y acciones que pueden asignarse a usuarios y grupos de usuarios.',
    'role_title_instructions' => 'Por lo general, un sustantivo singular, como "Editor" o "Administrador".',
    'search_utility_description' => 'Administra y revisa información importante sobre los índices de búsqueda de Statamic.',
    'session_expiry_enter_password' => 'Ingresa tu contraseña para continuar donde te quedaste.',
    'session_expiry_logged_out_for_inactivity' => 'Se ha cerrado tu sesión porque no tuviste actividad durante un rato.',
    'session_expiry_logging_out_in_seconds' => '¡Tu sesión ha estado inactiva durante un rato y se cerrará en :seconds segundos! Haz clic para extender tu sesión y seguir trabajando.',
    'session_expiry_new_window' => 'Se abrirá una ventana nueva. Vuelve aquí una vez que hayas iniciado sesión.',
    'tab_sections_instructions' => 'Los campos en cada sección se agruparán en pestañas. Crea nuevos campos, reutiliza campos existentes o importa grupos enteros de campos en conjuntos de campos existentes.',
    'taxonomies_blueprints_instructions' => 'Los términos en esta taxonomía pueden ser usados en cualquiera de estos planos.',
    'taxonomies_collections_instructions' => 'Las colecciones que usan esta taxonomía.',
    'taxonomy_configure_handle_instructions' => 'Se usa para hacer referencia a esta taxonomía en la interfaz. Es complicado cambiarlo más tarde.',
    'taxonomy_configure_intro' => 'Una taxonomía es un sistema de clasificación de datos en torno a un conjunto de características únicas, como "categoría" o "color".',
    'taxonomy_configure_title_instructions' => 'Recomendamos utilizar un sustantivo plural, como "Categorías" o "Etiquetas".',
    'taxonomy_next_steps_configure_description' => 'Configura nombres, asocia colecciones, define planos y más.',
    'taxonomy_next_steps_create_term_description' => 'Crea el primer término o escribe un puñado de términos de muestra, depende de ti.',
    'taxonomy_next_steps_documentation_description' => 'Obtén más información sobre taxonomías, cómo funcionan y cómo configurarlas.',
    'try_again_in_seconds' => '{0,1} Inténtalo de nuevo ahora. | Inténtalo de nuevo en :count segundos.',
    'updates_available' => '¡Hay actualizaciones disponibles!',
    'user_groups_handle_instructions' => 'Se usa para hacer referencia a este grupo de usuarios en la interfaz. Es complicado cambiarlo más tarde.',
    'user_groups_intro' => 'Los grupos de usuarios te permiten organizar usuarios y aplicar roles basados en permisos en conjunto.',
    'user_groups_role_instructions' => 'Asigna roles para otorgar a los usuarios de este grupo todos sus permisos correspondientes.',
    'user_groups_title_instructions' => 'Generalmente un sustantivo plural, como "Editores" o "Fotógrafos"',
    'user_wizard_account_created' => 'La cuenta de usuario ha sido creada.',
    'user_wizard_email_instructions' => 'La dirección de correo electrónico también sirve como nombre de usuario y debe ser única.',
    'user_wizard_intro' => 'Los usuarios pueden ser asignados a roles que personalizan sus permisos, acceso y habilidades dentro del Panel de control.',
    'user_wizard_invitation_body' => 'Activa tu nueva cuenta de Statamic en :site para empezar a administrar este sitio web. Por tu seguridad, el enlace caduca después de :expiry horas. Después de que pasen, tendrás que contacta al administrador del sitio para generar una nueva contraseña.',
    'user_wizard_invitation_intro' => 'Envía un correo de bienvenida con detalles de la actividad de la cuenta al nuevo usuario.',
    'user_wizard_invitation_share' => 'Copia estas credenciales y compártelas con <code>:email</code> a través de tu método preferido.',
    'user_wizard_invitation_share_before' => 'Después de crear el usuario, se te darán detalles para compartir <code>:email</code> través de tu método preferido.',
    'user_wizard_invitation_subject' => 'Activa tu nueva cuenta de Statamic en :site',
    'user_wizard_name_instructions' => 'Deja el nombre en blanco para dejar que el usuario lo llene.',
    'user_wizard_roles_groups_intro' => 'Los usuarios pueden ser asignados a roles que personalizan sus permisos, acceso y habilidades en todo el Panel de Control.',
    'user_wizard_super_admin_instructions' => 'Los superadministradores tienen control y acceso completos a todo en el Panel de Control. Concede este rol sabiamente.',
];
