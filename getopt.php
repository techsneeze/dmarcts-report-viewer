<?php
/*
 * Copyright (c) 2016, Etienne Samson
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

// Source : https://gist.github.com/tiennou/0a08eae329c34afe08300c93eaeaab0d

function my_getopt(&$arguments, $options, $longopts = array()) {
    global $argv;
    $args = $argv; array_shift($args); // drop argv[0]
    $arguments = array();
    $parsed = array();
    for ($i = 0; $i < count($args); $i++) {
        if ($args[$i][0] != "-") { // non-option, moving on
            $arguments[] = $args[$i];
            continue;
        }

        /*
         * getopt compat: getopt (arguably) accepts options as arguments to options.
         * if you want only non-options to be accepted, uncomment the second part
         */
        // does our next arg exist?
        $next_arg = (isset($args[$i + 1]) /* && $args[$i + 1][0] != "-" */);
        if ($args[$i][1] != "-") { // short opt
            $matches = null;
            if (($optpos = strpos($options, $args[$i][1])) !== FALSE) {
                if (!isset($options[$optpos + 1])
                        || $options[$optpos + 1] != ':') { // no argument
                    $parsed[$args[$i][1]] = false;
                    continue;
                } else if (!isset($options[$optpos + 2])
                        || $options[$optpos + 2] != ':') { // mandatory
                    if ($arg = substr($args[$i], 2)) {
                        $parsed[$args[$i][1]] = $arg;
                        continue;
                    } elseif ($next_arg) {
                        $parsed[$args[$i++][1]] = $args[$i];
                        continue;
                    }
                    break;
                } else { // optional
                    if ($next_arg) {
                        /* getopt compat: getopt doesn't handle space-separated
                        * optional arguments. replace false with $args[$i] to fix
                        */
                        $parsed[$args[$i++][1]] = false;//$args[$i];
                    } elseif ($arg = substr($args[$i], 2)) {
                        $parsed[$args[$i][1]] = $arg;
                    } else {
                        $parsed[$args[$i][1]] = false;
                    }
                    continue;
                }
            }

            $arguments[] = $args[$i]; // unrecognized option
            continue;
        }

        if (strlen($args[$i]) == 2) { // looking at --, abort
            $arguments = array_merge($arguments, array_slice($args, $i + 1));
            break;
        }

        $arg = substr($args[$i], 2); $arglen = strlen($arg);
        foreach ($longopts as $longopt) { // longopts
            if (substr_compare($arg, $longopt, 0, $arglen) == 0) {
                $mod = substr($longopt, $arglen);
                switch (strlen($mod)) {
                    case 0: // no argument
                        $parsed[$arg] = false;
                        continue 3;

                    case 1: // mandatory
                        if ($next_arg) {
                            $parsed[$arg] = $args[++$i];
                            continue 3;
                        }
                        break;

                    case 2: // optional
                        if ($next_arg) {
                            $parsed[$arg] = $args[++$i];
                        } else {
                            $parsed[$arg] = false;
                        }
                        continue 3;
                }
            }
        }

        $arguments[] = $args[$i]; // unrecognized option
        continue;
    }
    return $parsed;
}

?>