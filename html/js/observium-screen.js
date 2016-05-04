/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage js
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2015 Observium Limited
 *
 */

//$(document).ready(function()
//{
 screen_detect();
//});

function screen_detect(){
  var date = new Date();
  date.setTime(date.getTime() + 3600000); // 1 hour
  var options = ' expires=' + date.toUTCString() +'; path=/';
  
  if(document.cookie.indexOf('observium_screen_ratio') == -1){
    var screen_ratio = 1;
    if('devicePixelRatio' in window){
      screen_ratio = window.devicePixelRatio;
    }
    // store to cookie
    document.cookie = 'observium_screen_ratio=' + screen_ratio + ';' + options;
    document.cookie = 'observium_screen_resolution=' + screen.width + 'x' + screen.height + ';' + options;
    //console.log('screen_ratio = ' + screen_ratio);
    //console.log('screen_resolution = ' + screen.width + 'x' + screen.height);
    //console.log('screen_size = ' + window.innerWidth + 'x' + window.innerHeight);
    //if cookies are not blocked, reload the page
    //if(document.cookie.indexOf('observium_screen_ratio') != -1){
    //    window.location.reload();
    //}
  }
  // Calculate screen(window) size on every page load
  //document.cookie = 'observium_screen_size=' + window.innerWidth + 'x' + window.innerHeight + ';' + options;
  //document.cookie = 'observium_screen_size=' + document.documentElement.clientWidth + 'x' + document.documentElement.clientHeight + ';' + options;
}

// EOF

