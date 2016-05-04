<?php

/**
 * Sends statistics to an instance of the statsd daemon over UDP
 *
 * Make changes here: https://github.com/iFixit/statsd-php-client
 * See: https://github.com/etsy/statsd
 *

 Copyright (c) 2010 Etsy
 Copyright (c) 2012 iFixit

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be

 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.

 **/

// File dated 2014-10-18

class StatsD {
        /**
         * Name of our statsd-server
         * @var string
         */
   protected static $host = 'localhost';
   /**
    * UDP-port of the statsd-server
    * @var string
    */
   protected static $port = '8125';

   /**
    * Maximum payload we may cramp into a UDP packet
    */

   const MAX_PACKET_SIZE = 512;

   /**
    * If true, stats are added to a queue until a flush is triggered
    * If false, stats are sent immediately, one UDP packet per call
    *
    * @var bool
    */
   protected static $addStatsToQueue = false;

   /**
    * Internal queue of stats to be sent
    * @var array
    */
   protected static $queuedStats = array();

   /**
    * Internal representation of queued counters to be sent.
    * This is used to aggregate increment/decrements before sending them.
    * @var array
    */
   protected static $queuedCounters = array();

   /**
    * Log timing information
    *
    * @param string $stat The metric to in log timing info for.
    * @param float $time The ellapsed time (ms) to log
    * @param float $sampleRate the rate (0-1) for sampling.
    **/
   public static function timing($stat, $time, $sampleRate=1.0) {
      static::queueStats(array($stat => self::num($time) . "|ms"), $sampleRate);
   }

   /**
    * Report the current value of some gauged value.
    *
    * @param string|array $stat The metric to report on
    * @param float $value The value for this gauge
    */
   public static function gauge($stat, $value) {
      // echo("$stat || $value \n");
      static::queueStats(array($stat => self::num($value) . "|g"));
   }

   /**
    * Increments one stats counter
    *
    * @param string $stat The metric to increment.
    * @param float $sampleRate the rate (0-1) for sampling.
    **/
   public static function increment($stat, $sampleRate=1.0) {
      static::updateStat($stat, 1, $sampleRate);
   }

   /**
    * Decrements one counter.
    *
    * @param string $stat The metric to decrement.
    * @param float $sampleRate the rate (0-1) for sampling.
    **/
   public static function decrement($stat, $sampleRate=1.0) {
      static::updateStat($stat, -1, $sampleRate);
   }

   /**
    * Pause and collect all reported stats until flushStatsOutput() is called.
    */
   public static function pauseStatsOutput() {
      static::$addStatsToQueue = true;
   }

   /**
    * Send all stats generated AFTER a call to pauseStatsOutput()
    * and resume immediate sending again.
    */
   public static function flushStatsOutput() {
      static::$addStatsToQueue = false;
      static::sendAllStats();
   }

   /**
    * Updates a counter by an arbitrary amount.
    *
    * @param string $stat The metric to update.
    * @param float $delta The amount to increment/decrement the metric by.
    * @param float $sampleRate the rate (0-1) for sampling.
    **/
   public static function updateStat($stat, $delta=1, $sampleRate=1.0) {
      $deltaStr = self::num($delta);
      if ($sampleRate < 1) {
         if ((mt_rand() / mt_getrandmax()) <= $sampleRate) {
            static::$queuedStats[] = "$stat:$deltaStr|c|@". self::num($sampleRate);
         }
      } else {
         if (!isset(static::$queuedCounters[$stat])) {
            static::$queuedCounters[$stat] = 0;
         }
         static::$queuedCounters[$stat] += $delta;
      }

      if (!static::$addStatsToQueue) {
         static::sendAllStats();
      }
   }

   /**
    * Deprecated, works, but will be removed in the future.
    *
    * @param string|array $stats The metric(s) to update. Should be either a string or an array of strings.
    * @param float $delta The amount to increment/decrement each metric by.
    * @param float $sampleRate the rate (0-1) for sampling
    * @deprecated in favour of updateStat
    */
   public static function updateStats($stats, $delta=1, $sampleRate=1.0) {
      if (!is_array($stats)) {
         self::updateStat($stats, $delta, $sampleRate);
         return;
      }
      foreach ($stats as $stat) {
         self::updateStat($stat, $delta, $sampleRate);
      }
   }

   /**
    * Add stats to the queue or send them immediately depending on
    * self::$addStatsToQueue
    *
    * @param array $data The data to be queued.
    * @param float $sampleRate the rate (0-1) for sampling
    */
   protected static function queueStats($data, $sampleRate=1.0) {
      if ($sampleRate < 1) {
         foreach ($data as $stat => $value) {
            if ((mt_rand() / mt_getrandmax()) <= $sampleRate) {
               static::$queuedStats[] = "$stat:$value|@". self::num($sampleRate);
            }
         }
      } else {
         foreach ($data as $stat => $value) {
            static::$queuedStats[] = "$stat:$value";
         }
      }

      if (!static::$addStatsToQueue) {
         static::sendAllStats();
      }
   }

   /**
    * Flush the queue and send all the stats we have.
    */
   protected static function sendAllStats() {
      if (empty(static::$queuedStats) && empty(static::$queuedCounters))
         return;

      foreach (static::$queuedCounters as $stat => $value) {
         $line = "$stat:$value|c";
         static::$queuedStats[] = $line;
      }

      self::sendLines(static::$queuedStats);

      static::$queuedStats = array();
      static::$queuedCounters = array();
   }

   /**
    * Squirt the metrics over UDP
    *
    * @param array $data the data to be sent.
    */
   protected static function sendAsUDP($data) {
      // Wrap this in a try/catch -
      // failures in any of this should be silently ignored
      try {
         $host = static::$host;
         $port = static::$port;
         $fp = fsockopen("udp://$host", $port, $errno, $errstr);
         if (! $fp) { return; }
         // Non-blocking I/O, please.
         stream_set_blocking($fp, 0);
         fwrite($fp, $data);
         fclose($fp);
      } catch (Exception $e) {
      }
   }

   /**
    * Send these lines via UDP in groups of self::MAX_PACKET_SIZE bytes
    * Sending UDP packets bigger than ~500-1000 bytes will mean the packets
    * get fragmented, and if ONE fragment doesn't make it, the whole datagram
    * is thrown out.
    *
    * @param array $lines The lines to be sent to the stats-Server
    */
   protected static function sendLines($lines) {
      $out = array();
      $chunkSize = 0;
      $i = 0; $lineCount = count($lines);
      while ($i < $lineCount) {
         $line = $lines[$i];
         $len = strlen($line) + 1;
         $chunkSize += $len;
         if ($chunkSize > self::MAX_PACKET_SIZE) {
            static::sendAsUDP(implode("\n", $out));
            $out = array($line);
            $chunkSize = $len;
         } else {
            $out[] = $line;
         }
         $i++;
      }
      static::sendAsUDP(implode("\n", $out));
   }

   /**
    * This is the fastest way to ensure locale settings don't affect the
    * decimal separator. Really, this is the only way (besides temporarily
    * changing the locale) to really get what we want.
    *
    * @param string $value the value to be "translated" to the needed locale
    * @return string the "translated" value
    */
   protected static function num($value) {
      return strtr($value, ',', '.');
   }
}
