<?php
if (!defined('ABSPATH')) {
    exit;
}
class SATL_Helper{
    static function update_or_create_option($option, $value)
    {
        if (get_option($option)) {
            update_option($option, $value);
        } else {
            add_option($option, $value);
        }
    }
    static function time_elapsed_string($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => __('year','satl'),
            'm' => __('month','satl'),
            'w' => __('week','satl'),
            'd' => __('day','satl'),
            'h' => __('hours','satl'),
            'i' => __('min','satl'),
            's' => __('sec','satl'),
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . __(' ago ','satl') :__('just now','satl') ;
    }
    static function get_status_label($status){
        switch ($status){
            case 'pending':
                return __('Pending','satl');
                case 'done':
                return __('Done','satl');
            default:
                return __('Unknown','satl');
        }
    }

}