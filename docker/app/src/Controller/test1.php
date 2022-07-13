<?php
namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;

class test1
{
    public function number(): Response
    {
        $number = 3;
        

        return new Response(
            '<html><body>broj: '.$number. " " . $firestoreClient'</body></html>'
        );
    }
}


?>