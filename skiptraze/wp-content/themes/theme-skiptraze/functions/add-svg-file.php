<?php
    add_filter('wp_check_filetype_and_ext', 'my_file_and_ext_webp', 10, 4);
    function my_file_and_ext_webp($types, $file, $filename, $mimes)
    {
        if (false !== strpos($filename, '.svg')) {
            $types['ext']  = 'svg';
            $types['type'] = 'image/svg+xml';
        }
        return $types;
    }
    function my_custom_mime_types($mimes)
    {
        // New allowed mime types.
        $mimes['svg'] = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        // Optional. Remove a mime type.
        unset($mimes['exe']);
        return $mimes;
    }
    add_filter('upload_mimes', 'my_custom_mime_types', 10, 1);
?>