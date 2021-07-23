<?php

/**
 * index.php
 *
 * Check if domain has GSuite and also has SPF or DKIM well set up
 *
 * @package    CheckGSuiteDNS
 * @author     Fábio Nunes <fabio.nunes@wplus.pt>
 * @copyright  2021 WPlus
 * @version    1.0.0
 * @link       https://github.com/fabiomsnunes/CheckGSuiteDNS
 *
 * CheckGSuiteDNS is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * CheckGSuiteDNS is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with CheckGSuiteDNS. If not, see http://www.gnu.org/licenses/.
 *
 *
 */

$domains = file('domains.txt', FILE_IGNORE_NEW_LINES);

foreach ($domains as $domain) {
    if (hasGSuite($domain) && !checkSPForDKIM($domain)) {
        file_put_contents('leads.txt', $domain . PHP_EOL, FILE_APPEND);
    }
}


function hasGSuite($domain)
{
    getmxrr($domain, $mx_records);
    return (in_array_r('alt1.aspmx.l.google.com',  $mx_records) || in_array_r('alt2.aspmx.l.google.com',  $mx_records));
}


function checkSPForDKIM($domain)
{
    $dns_records = dns_get_record($domain, DNS_TXT);
    return (in_array_r('_spf.google.com', $dns_records, $strict = false) || in_array_r('v=DKIM1', $dns_records, $strict = false));
}


function in_array_r($needle, $haystack, $strict = true)
{
    foreach ($haystack as $item) {
        if (($strict ? strtolower($item) == $needle : strpos(strtolower($item), $needle) !== false) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}
