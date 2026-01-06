@if (isset($icon))
    @php
        $iconFile = resource_path("svg/lucide/{$icon}.svg");
        $svgContent = file_exists($iconFile) ? file_get_contents($iconFile) : '';
        if ($svgContent) {
            // Add class if specified
            if (isset($class)) {
                $svgContent = preg_replace('/<svg([^>]*)>/', '<svg$1 class="'.$class.'">', $svgContent);
            }
            echo $svgContent;
        } else {
            // Fallback for missing icon
            echo '<svg class="' . ($class ?? 'w-5 h-5') . '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 2L3.5 4.5L6 7L8.5 4.5L6 2z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 22L9 12" /></svg>';
        }
    @endphp
@endif