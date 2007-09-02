<?php
    /**
     * @archivo   modules/file/lang/es.lang.php
     * @autor zero <zero@nzeo.com>
     * @sumario Paquete del idioma español para archivos adjuntos
     **/

    $lang->file = 'Archivo adjunto';
    $lang->file_name = 'Nombre del archivo';
    $lang->file_size = 'Tamaño del archivo';
    $lang->download_count = 'Cantidad Bajado';
    $lang->status = 'Estado';
    $lang->is_valid = 'Valido';
    $lang->is_stand_by = 'En espera';
    $lang->file_list = 'Lista de archivos adjuntos';
    $lang->allowed_filesize = 'Límite del tamaño del archivo adjunto';
    $lang->allowed_attach_size = 'Límite del tamaño total de los archivos adjuntos por documento';
    $lang->allowed_filetypes = 'Tipos de archivos permitidos';

    $lang->about_allowed_filesize = 'Puede definir el límite deltamaño del archivo adjunto. (exceptuando el administrador)';
    $lang->about_allowed_attach_size = 'Pueude definir el límite del tamaño total de los archivos adjuntos por documento. (exceptuando el administrador)';
    $lang->about_allowed_filetypes = 'Puede definir las extensiones de los archivos permitidos. Para permitir una extensión use "*.extensión". Para permitir más de una extensión use ";".<br />ej) *.* o *.jpg;*.gif;etc.<br />(exceptuando el administrador)';

    $lang->cmd_delete_checked_file = 'Eliminar el archivo seleccionado';
    $lang->cmd_move_to_document = 'Moverse a doncumento';
    $lang->cmd_download = 'Bajar';

    $lang->msg_cart_is_null = 'Seleccione el archivo a eliminar';
    $lang->msg_checked_file_is_deleted = 'Total de %d archivos eliminados';
    $lang->msg_exceeds_limit_size = 'Ha excedido el límite del tamaño total de los archivos adjuntos';

    $lang->search_target_list = array(
        'filename' => 'Nombre del archivo',
        'filesize' => 'Tamaño del archivo (Byte, sobre)',
        'download_count' => 'Bajados (Sobre)',
        'regdate' => 'La fecha registrada',
        'ipaddress' => 'Dirección IP',
    );
?>
