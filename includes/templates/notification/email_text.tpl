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
 * ENTITY_NAME, ENTITY_DESCRIPTION,
 * DEVICE_HOSTNAME, DEVICE_HARDWARE, DEVICE_OS, DEVICE_LOCATION, DEVICE_UPTIME
 */
------------------------------------
{{ALERT_STATE}}
{{ALERT_MESSAGE}}
------------------------------------
Entity:      {{ENTITY_NAME}}
{{#ENTITY_DESCRIPTION}}
Description: {{ENTITY_DESCRIPTION}}
{{/ENTITY_DESCRIPTION}}
{{#CONDITIONS}}
Conditions:  {{{CONDITIONS}}}
{{/CONDITIONS}}
Metrics:     {{METRICS}}
Duration:    {{DURATION}}
------------------------------------

------------------------------------
Device:      {{DEVICE_HOSTNAME}}
Hardware:    {{DEVICE_HARDWARE}}
OS:          {{DEVICE_OS}}
Location:    {{DEVICE_LOCATION}}
Uptime:      {{DEVICE_UPTIME}}
------------------------------------
