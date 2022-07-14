<?php

namespace App\Controller;

use Error;
use Google\Cloud\Firestore\FirestoreClient;
use Symfony\Component\HttpFoundation\Response;

class test1
{
    /**
     * @Route("/test1/{projectid}", name="app_lucky_number")
     */
    public function firestoredata(): Response
    {
        $projectId = 'matejgrubisicgiftcard';

        $fieldnum = random_int(1000000, 9999999);



        function Randomstring()
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 10; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        $randomid = Randomstring();


        $firestore = new FirestoreClient([
            'projectId' => $projectId,
        ]);

        $data = [
            'broj' => $fieldnum
        ];
        $num = 0;
        $docId = [];
        $idRef = $firestore->collection('giftcard1');
        $snapshot = $idRef->documents();
        foreach ($snapshot as $id) {
            $docId[$num] = $id->id();
            # printf($docId[$num] . " "); << print $docId
            $num++;
        };

        $doclength = count($docId);
        /* testira san nesto neradi ovo 
        for ($i = 0; $i < $doclength; $i++) {
            for ($i2 = 0; $i2 < $doclength; $i2++) {
                if ($docId[$i] == $docId[$i2]) {
                    $randomid = Randomstring();
                    $docId[$i] = $randomid;
                }
            };
            printf($docId[$i] . "radi" . " ");
        };
        */
        $firestore->collection('giftcard1')->document($randomid)->set($data);


        return new Response(
            '<html><body>field number: ' . $fieldnum . " " . 'uspilo je' . '</body></html>'
        );
    }
}
