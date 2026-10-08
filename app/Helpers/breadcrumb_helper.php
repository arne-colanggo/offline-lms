<?php
// app/Helpers/breadcrumb_helper.php

if (!function_exists('breadcrumb_items')) {
    /**
     * Build breadcrumb items from the current URL segments.
     *
     * @param array  $labels   Custom labels. Key by segment ('sections')
     *                         or by full path ('sections/12') for dynamic pages.
     * @param string $homeUrl  Where the "Home" crumb points to.
     * @param array  $skip     Segments to leave out entirely (e.g. 'admin', 'index').
     * @param array  $noLink   Segments shown as plain text because they have no
     *                         page of their own (e.g. 'edit', 'create').
     *
     * @return array<int, array{label:string, url:?string, active:bool}>
     */
    function breadcrumb_items(
        array $labels = [],
        string $homeUrl = 'dashboard',
        array $skip = ['index'],
        array $noLink = ['edit', 'create', 'update', 'delete', 'view', 'show']
    ): array {
        $segments = array_values(array_filter(service('uri')->getSegments()));
        $items = [];

        // Home crumb
        $items[] = [
            'label' => $labels['home'] ?? 'Home',
            'url' => base_url($homeUrl),
            'active' => false,
        ];

        $path = '';
        foreach ($segments as $segment) {
            $path .= ($path === '' ? '' : '/') . $segment;

            if (in_array($segment, $skip, true)) {
                continue;
            }

            // Label priority: full path > segment > numeric id > humanized segment
            if (isset($labels[$path])) {
                $label = $labels[$path];
            } elseif (isset($labels[$segment])) {
                $label = $labels[$segment];
            } elseif (ctype_digit($segment)) {
                $label = 'Details';
            } else {
                $label = ucwords(str_replace(['-', '_'], ' ', urldecode($segment)));
            }

            $items[] = [
                'label' => $label,
                'url' => in_array($segment, $noLink, true) ? null : base_url($path),
                'active' => false,
            ];
        }

        // Last crumb = current page (no link)
        $last = count($items) - 1;
        $items[$last]['active'] = true;
        $items[$last]['url'] = null;

        return $items;
    }
}