<?php
/**
 * Kravyo - Google Maps JavaScript API loader partial
 *
 * Usage: <?php View::partial('google_maps_loader', ['portal' => 'customer']); ?>
 *   portal: 'customer' | 'delivery' | 'chef'  (decides which session-scoped proxy URLs to use)
 *
 * Loads Google's official async bootstrap loader (google.maps.importLibrary) once,
 * exposes window.KRAVYO_MAPS_CONFIG and includes public/assets/js/kravyo-maps.js.
 */
if (!empty($GLOBALS['__kravyo_maps_loader_included'])) {
    return;
}
$GLOBALS['__kravyo_maps_loader_included'] = true;

$portal   = $portal ?? 'customer';
$prefix   = match ($portal) {
    'delivery' => '/delivery',
    'chef'     => '/chef',
    default    => '',
};
$mapsKey  = class_exists('GoogleMaps') ? GoogleMaps::browserKey() : '';
$mapsConf = [
    'enabled'       => $mapsKey !== '',
    'mapId'         => class_exists('GoogleMaps') ? GoogleMaps::mapId() : 'DEMO_MAP_ID',
    'defaultCenter' => [
        'lat' => defined('GOOGLE_MAPS_DEFAULT_LAT') ? (float) GOOGLE_MAPS_DEFAULT_LAT : 21.1702,
        'lng' => defined('GOOGLE_MAPS_DEFAULT_LNG') ? (float) GOOGLE_MAPS_DEFAULT_LNG : 72.8311,
    ],
    'routeUrl'      => url($prefix . '/api/maps/route'),
    'geocodeUrl'    => url($prefix . '/api/maps/geocode'),
];
?>
<script>
window.KRAVYO_MAPS_CONFIG = <?= json_encode($mapsConf, JSON_UNESCAPED_SLASHES) ?>;
</script>
<?php if ($mapsKey !== ''): ?>
<script>
  (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
    key: <?= json_encode($mapsKey) ?>,
    v: "weekly",
    region: "IN",
    language: "en"
  });
</script>
<?php endif; ?>
<script src="<?= ASSET_URL ?>/js/kravyo-maps.js?v=<?= time() ?>"></script>
