/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage templates
 * @copyright  (C) 2006-2015 Observium Limited
 *
 */
/**
 * Used keys:
 * ALERT_STATE, ALERT_URL, ALERT_MESSAGE, CONDITIONS, METRICS, DURATION,
 * ENTITY_LINK, ENTITY_DESCRIPTION, ENTITY_GRAPHS,
 * DEVICE_LINK, DEVICE_HARDWARE, DEVICE_OS, DEVICE_LOCATION, DEVICE_UPTIME
 */
<html>
<head>
  <title>Observium Alert</title>
  <style type="text/css">
  .observium{
    width:100%; max-width: 500px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px;
    border:1px solid #DDDDDD; background-color:#FAFAFA; font-size: 13px; color: #777777;
  }
  .header{ font-weight: bold; font-size: 16px; padding: 5px; color: #555555; }
  .red { color: #cc0000; }
  #deviceinfo tr:nth-child(odd) { background: #ffffff; }
  </style>
</head>
<body>
<table class="observium">
  <tbody>
    <tr>
      <td>
        <table class="observium" id="deviceinfo">
  <tbody>
    <tr><td class="header">{{ALERT_STATE}}</td><td><a style="float: right;" href="{{{ALERT_URL}}}">Modify</a></td></tr>
    <tr><td><strong>Alert</strong></td><td class="red">{{ALERT_MESSAGE}}</td></tr>
    <tr><td><strong>Entity</strong></td><td>{{{ENTITY_LINK}}}</td></tr>
    {{#ENTITY_DESCRIPTION}}
    <tr><td><strong>Descr</strong></td><td>{{ENTITY_DESCRIPTION}}</td></tr>
    {{/ENTITY_DESCRIPTION}}
    {{#CONDITIONS}}
    <tr><td><strong>Conditions</strong></td><td>{{{CONDITIONS}}}</td></tr>
    {{/CONDITIONS}}
    <tr><td><strong>Metrics</strong></td><td>{{{METRICS}}}</td></tr>
    <tr><td><strong>Duration</strong></td><td>{{DURATION}}</td></tr>
    <tr><td colspan="2" class="header">Device</td></tr>
    <tr><td><strong>Device</strong></td><td>{{{DEVICE_LINK}}}</td></tr>
    <tr><td><strong>Hardware</strong></td><td>{{DEVICE_HARDWARE}}</td></tr>
    <tr><td><strong>Operating System</strong></td><td>{{DEVICE_OS}}</td></tr>
    <tr><td><strong>Location</strong></td><td>{{DEVICE_LOCATION}}</td></tr>
    <tr><td><strong>Uptime</strong></td><td>{{DEVICE_UPTIME}}</td></tr>
  </tbody>
        </table>
      </td>
    </tr>
    {{#ENTITY_GRAPHS}}
    <tr><td><center>{{{ENTITY_GRAPHS}}}</center></td></tr>
    {{/ENTITY_GRAPHS}}
  </tbody>
</table>
</body>
</html>
