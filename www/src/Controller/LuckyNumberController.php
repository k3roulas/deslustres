<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LuckyNumberController {

    /** 
     * @Route("/lucky/number")
     */
    public function number() {
        $number = \random_int(0, 1000);

        return new Response(
            "<html><body>Lucky number : $number</body></html>"
        );
    }


}