<?php

if (KIS_ENV == 'DEV') {
    $config = [
        'user' => [ ['192.168.1.92', '11556'] ],
        'sdk' => [ ['192.168.1.92', '11556'] ],
    ];
} else  if (KIS_ENV == 'ONLINE_TEST'){
    $config = [
        'user' => [ ['172.16.255.17', '11211'] ],
        'sdk' => [ ['172.16.255.17', '11211'] ],
    ];
} else if(KIS_ENV == 'ONLINE'){
    $config = [
        'user' => [ ['172.16.255.10', '11211'] ],
        'sdk' => [ ['172.16.255.10', '11211'] ],
    ];
}

return $config;