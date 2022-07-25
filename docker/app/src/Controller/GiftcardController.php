<?php

namespace App\Controller;

use Google\Cloud\Firestore\FirestoreClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#php bin/console ca:cl
class GiftcardController extends AbstractController
{
    const PROJECTID = 'matejgrubisicgiftcard';
    /** 
     * @Route("/giftcard/create", methods={"POST"})
     */
    public function giftCreate(Request $request): JsonResponse
    {
        #makni iz body-a isUsed jer to tako neradi
        $data = $request->toArray();

        $firestore = new FirestoreClient([
            'projectId' => self::PROJECTID,
        ]);


        $document = $firestore->collection($_ENV['COLLECTION'])->newDocument();
        $document->create($data);



        return new JsonResponse(
            $document->snapshot()->data()
        );
        #works 
    }

    /** 
     * @Route("/giftcard/search/{id}", methods={"GET"})
     */
    public function giftGet($id): JsonResponse
    {

        $firestore = new FirestoreClient([
            'projectId' => self::PROJECTID,
        ]);

        $doc = $firestore->collection($_ENV['COLLECTION'])->document($id);

        $snapshot = $doc->snapshot();
        #2315b7f8ed6b4e09b24f
        if (!$snapshot->exists()) {
            printf('Document %s does not exist!' . PHP_EOL, $snapshot->id());
        }

        return new JsonResponse(
            $snapshot->data()
        );
    }

    /** 
     * @Route("/giftcard/redeem/{id}", methods={"POST"})
     */
    public function giftRedeem($id, Request $request): JsonResponse
    {
        #u body ubaci money koliko treba oduzet
        #validacija rjesi da validira dobro sve i da provjeri jeli neko unia -50 npr ili +50
        $firestore = new FirestoreClient([
            'projectId' => self::PROJECTID,
        ]);

        $newNum = $request->toArray();

        $doc = $firestore->collection($_ENV['COLLECTION'])->document($id);
        $snapshot = $doc->snapshot();

        $oldNum = $snapshot->data();
        $oldNum = $oldNum['currency']['amount'];
        
        $newNum = $newNum['amount'];
        
        if (!$snapshot->exists()) {
            return new JsonResponse(
                ["This giftcard does not exist"]
            );
        } else {            
            if($newNum < 0){
                return new JsonResponse(
                  "The amount you want to use is invalid, please try again."  
                );
            }
            else{
                $data = $oldNum - $newNum;
                if($data < 0){
                    return new JsonResponse(
                        "There isn't enough money on the card for this transaction." 
                    );
                }
                else{
                    $doc->update([
                        ['path' => 'currency.amount', 'value' => $data]
                    ]);
        
                    $snapshot = $doc->snapshot();
                    return new JsonResponse(
                        $snapshot->data()
                    );
                }
            }

            
            
            #ubaci da nemore bit used<napravljeno|| i ubaci nacin za pare micat <napravljeno isto
            
        }

        #$doc->delete();


    }
    /** 
     * @Route("/giftcard/invalidate/{id}", methods={"PATCH"})
     */
    public function giftInvalidate($id): JsonResponse
    {
        #PATCH change bool

        $firestore = new FirestoreClient([
            'projectId' => self::PROJECTID,
        ]);

        $doc = $firestore->collection($_ENV['COLLECTION'])->document($id);

        $snapshot = $doc->snapshot();

        $data = $snapshot->data();
        $array = json_encode($data['isValid'], true);
        #print_r($array);

        if (!$snapshot->exists()) {
            return new JsonResponse(
                "There is no giftcard"
            );
        } else{
            #ubaci da nemore invalidateat ako je vec invalid < rjeseno s donjin kodon
            if($array != 'false'){
                $doc->update([
                    ['path' => 'isValid', 'value' => false]
                ]);
                $snapshot = $doc->snapshot();
                return new JsonResponse(
                    $snapshot->data()
                );
            }
            else{
                return new JsonResponse(
                    "This giftcard is already invalid"
                );
            }
        }
    }
}
