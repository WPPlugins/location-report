=== Location Report ===
Contributors: christian_feldbauer
Tags: geo, current location, latest position, travel route, kml, map, shortcode
Requires at least: 3.0.1
Tested up to: 4.7.3
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides a shortcode to update your location and generates kml files for your latest location and your route.

== Description ==

Provides the [locationreport ...] shortcode that lets you update your current location. It generates two kml files. The first has a placemark of your current location and the second one shows the route along all your location reports. Those kml files can be displayed by one of the many available map plugins (e.g., OpenStreetMap or Flexible Map). Note, this plugin itself does NOT provide a map. However, it provides a simple, universal (no GUI interaction necessary, so you can report your position even when posting by email), portable (no db, just kml files) and modular way of position reporting and together with a map plugin you can show your latest position and/or your travel route in a map widget or page.


== Installation ==

1. Upload this plugin to your /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use the [locationreport ...] shortcode in your posts whenever you want to update your location.
4. Use a map plugin to display the generated kml files with your latest position and/or your (travel) route.


== Frequently Asked Questions ==

= How to use the [locationreport] shortcode? =

Here are some examples to report a latitude of 25° 51' N and a longitude of 135° 7.3' W as your current location: 

  [locationreport lat="25.85" lon="-135.1217"] ... Here the coordinates are given in decimal degrees. Positive values for N and E, negative for S and W. Note, as this format does not have any blanks, the quotes can be omitted:
   
  [locationreport lat=25.85 lon=-135.1217] ... This is fine as well.
  
  [locationreport lat="25° 51' N" lon="135° 7.3' W"] ... If you really love typing (and searching for the degree character)... Coordinates are given as a triplet of degrees (positive integer), minutes (decimal) and cardinal direction.
  
  [locationreport lat=25°51'N lon=135°7.3'W] ... Without blanks the quotes are not needed. 
  
  [locationreport lat="25 51 N" lon="135 7.3 W"] ... The degree and minute characters are not needed. 
  
  [locationreport lat="25 51N" lon="135 7.3W"] ... Is okay as well.
  
  [locationreport lat="25N 51" lon="135W 7.3"] ... And so is this.
  
  [locationreport lat="N25 51" lon="W135 7.3"] ... And this.
  
  [locationreport lat=25N51 lon=135W7.3] ... And also this.
  
Choose your favorite coordinate format and put this shortcode with your actual location in a blog entry. That's it. Giving the position as degrees, minutes and seconds is not supported yet.


= What does this shortcode do? =

When the blog entry is saved (published or private), the shortcode is replaced by a short paragraph: 'Position reported on DATE at TIME UTC: LAT, LON.' and the kml files for your route and your current location are updated. Date and time is taken from the post's publication time. 


= Where can I find these kml files? =

The kml files are in your blog's upload directory in the subfolder 'locationreport', normally at '/wp-content/uploads/locationreport/'. The files are named 'latest.kml' and 'route.kml'.


= How do I display those kml files in a map on my blog? =

There are many map plugins available for WordPress. You need one that is capable of displaying kml files. I successfully tested displaying the locationreport kml files using the 'OSM' plugin, the 'Flexible Map' plugin, and 'Geo Mashup'. Those three plugins provide shortcodes to put a map on a page or a text widget. Refer to the documentation of your favorite map plugin for more information on how to display kml files.


= I made a mistake when I posted the [locationreport ...]. Can I correct the coordinates in the kml files? =

Yes that is possible. Log in at your blog and click on 'Editor' under the 'Plugin' section. Select 'Location Report' as the plugin to be edited. Then select the file 'latest.kml.txt' or 'route.kml.txt' (the .txt extensions are just a workaround to get the kml files included in the file list). Search for your mistake and correct it. Save the file by clicking on 'Update File'.


= I'd like to have a different icon for my latest location. How can I change the  placemark icon? =

Kml files can include styles to define placemark icons (as well as line styles) and both locationreport kml files are prepared with empty style definitions for easy customization. To change how the placemark for your latest location is displayed, you can add your IconStyle definition in the file 'style.kml.txt' inside the Style element called 'locationreportLatestStyle' using the Editor under Plugins as described earlier. This definition will effect both kml files, latest.kml and route.kml. 

You also can change the placemark icon for the older location reports as well as the line style for your route. Simply add your IconStyle and LineStyle definitions in the file 'style.kml.txt' inside the Style element called 'locationreportRouteStyle'.

Don't change the styles inside latest.kml or route.kml (or their symbolic links latest.kml.txt or route.kml.txt) directly as these files will be overwritten when you use the [locationreport] shortcode the next time.

Here are some example style definitions:

    <Style id="locationreportLatestStyle"> 
      <IconStyle>
        <Icon>
          <href>http://yourblog.com/icon.jpg</href>
        </Icon>
      </IconStyle> 
    </Style>
    
    <Style id="locationreportRouteStyle">
      <IconStyle>
        <color>ff0088ff</color>
      </IconStyle>
      <LineStyle>
        <color>ff0000ff</color>
        <width>6</width>
      </LineStyle>
    </Style>

For a complete documentation on how to define styles refer to the [KML reference](https://developers.google.com/kml/documentation/kmlreference).


== Changelog ==

### 1.0.2, 2017-04-11

* documentation cleanup

### 1.0.1, 2017-04-10

* added: documentation in readme.txt

