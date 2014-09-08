<?php
/**
 * Main cluster js file.
 *
 * Server Side Map Clustering
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     API Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */
?>

// Initialize the Ushahidi namespace
Ushahidi.baseURL = "<?php echo url::site(); ?>";
Ushahidi.markerRadius = <?php echo $marker_radius; ?>;
Ushahidi.markerOpacity = <?php echo $marker_opacity; ?>;
Ushahidi.markerStokeWidth = <?php echo $marker_stroke_width; ?>;
Ushahidi.markerStrokeOpacity = <?php echo $marker_stroke_opacity; ?>;

// Default to timespan
var startTime = <?php echo $active_startDate ?>;
var endTime = <?php echo $active_endDate ?>;

// To hold the Ushahidi.Map reference
var map = null;

jQuery(function() {
    var reportsURL = "<?php echo Kohana::config('settings.allow_clustering') == 1 ? "json/cluster" : "json"; ?>";

    // Render thee JavaScript for the base layers so that
    // they are accessible by Ushahidi.js
    <?php echo map::layers_js(FALSE); ?>

    // Map configuration
    var config = {

        // Zoom level at which to display the map
        zoom: <?php echo Kohana::config('settings.default_zoom'); ?>,

        // Redraw the layers when the zoom level changes
        redrawOnZoom: <?php echo Kohana::config('settings.allow_clustering') == 1 ? "true" : "false"; ?>,

        // Center of the map
        center: {
            latitude: <?php echo Kohana::config('settings.default_lat'); ?>,
            longitude: <?php echo Kohana::config('settings.default_lon'); ?>
        },

        // Map controls
        mapControls: [
            new OpenLayers.Control.Navigation({ dragPanOptions: { enableKinetic: true } }),
            new OpenLayers.Control.Attribution(),
            new OpenLayers.Control.Zoom(),
            new OpenLayers.Control.MousePosition({
                div: document.getElementById('mapMousePosition'),
                formatOutput: Ushahidi.convertLongLat
            }),
            new OpenLayers.Control.Scale('mapScale'),
            new OpenLayers.Control.ScaleLine(),
            new OpenLayers.Control.LayerSwitcher()
        ],

        // Base layers
        baseLayers: <?php echo map::layers_array(FALSE); ?>,

        // Display the map projection
        showProjection: true,

        reportFilters: {
            s: startTime,
            e: endTime,
            sharing: "main"
        }

    };

    // Initialize the map
    map = new Ushahidi.Map('map', config);
    map.addLayer(Ushahidi.GEOJSON, {
        name: "<?php echo Kohana::lang('ui_main.reports'); ?>",
        url: reportsURL,
        transform: false
    }, true, true);


});
