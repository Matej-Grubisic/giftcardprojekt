<?php

namespace App\Controller;

use Doctrine\Migrations\Configuration\Migration\JsonFile;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\FieldValue;
use Google\Type\DayOfWeek;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Error\UndefinedMethodError;

#php bin/console ca:cl
class GiftcardController extends AbstractController
{
    const PROJECTID = 'matejgrubisicgiftcard';
    /** 
     * @Route("/giftcard/create", methods={"POST"})
     */
    public function giftCreate(Request $request): JsonResponse
    {
        
        $data = $request->toArray();

        $firestore = new FirestoreClient([
            'projectId' => self::PROJECTID,
        ]);


        $document = $firestore->collection($_ENV['COLLECTION'])->newDocument();
        #echo $data['type'];
        if($data['value']['amount'] < 0){
            return new JsonResponse(
                "This giftcard has an invalid value, please try again."
            );
        }
        if($data['type'] != "DIGITAL"){
            return new JsonResponse(
                "This giftcard has an invalid type, please try again."
            );
        }
        $validData = [
            "type"=> $data['type'],
            "value"=>[
                "amount" => $data['value']['amount'],
                "currency" => $data['value']['currency']
            ],
            "transaction"=> [],
            "isValid" => true
        ];
        
        $document->create($validData);
        $addData = ["amount:" . " " . $data['value']['amount'] . " " . "date:" . " " . $document->snapshot()->createTime()];
        $document->update([
            ['path' => 'transaction.create', 'value' => FieldValue::arrayUnion($addData)]
        ]);


        return new JsonResponse(
            ["id" => $document->snapshot()->id(), ...$document->snapshot()->data()]
        ); 
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
        
        $firestore = new FirestoreClient([
            'projectId' => self::PROJECTID,
        ]);


        $givenAmount = $request->toArray();

        $doc = $firestore->collection($_ENV['COLLECTION'])->document($id);
        $snapshot = $doc->snapshot();

        $currentNum = $snapshot->data();
        $currentNum = $currentNum["value"]["amount"];

        $data = $snapshot->data();
        #$array = json_encode($data['isValid'], true);
        $array = $data["isValid"];
        
         
        if($array === false){
            return new JsonResponse(
                "This giftcard is invalid"
            );
        }
        
        $givenAmount = $givenAmount["amount"];

        if (!$snapshot->exists()) {
            return new JsonResponse(
                "This giftcard does not exist"
            );
        }

        if ($givenAmount < 0) {
            return new JsonResponse(
                "The amount you want to use is invalid, please try again."
            );
        }

        $data = $currentNum - $givenAmount;
        if ($data < 0) {
            return new JsonResponse(
                "There isn't enough money on the card for this transaction."
            );
        }

        $addData = ["amount:" . " " . $data . " " . "date:" . " " . $doc->snapshot()->updateTime()];

        $doc->update([
            ['path' => 'value.amount', 'value' => $data]
        ]);
        $doc->update([
            ['path' => 'transaction.redeem', 'value' => FieldValue::arrayUnion($addData)]
        ]);

        $snapshot = $doc->snapshot();
        return new JsonResponse(
            $snapshot->data()
        );
        #ubaci da nemore bit used<napravljeno|| i ubaci nacin za pare micat <napravljeno isto
    }
    /** 
     * @Route("/giftcard/invalidate/{id}", methods={"PATCH"})
     */
    public function giftInvalidate($id): JsonResponse
    {

        $firestore = new FirestoreClient([
            'projectId' => self::PROJECTID,
        ]);


        $doc = $firestore->collection($_ENV['COLLECTION'])->document($id);

        $snapshot = $doc->snapshot();

        $data = $snapshot->data();
        
        #print_r($array);

        if (!$snapshot->exists()) {
            return new JsonResponse(
                "There is no giftcard"
            );
        }

        $addData = ["amount:" . " " . $data['value']['amount'] . " " . "date:" . " " . $doc->snapshot()->updateTime()];
        #$array = json_encode($data['isValid'], true);
        $array = json_encode($data['isValid'], true);

        if ($array != 'false') {
            $doc->set([
                'isValid'=> false
            ],['merge' => true]);
            $doc->update([
                ['path' => 'transaction.invalidate', 'value' => FieldValue::arrayUnion($addData)]
            ]);
            $snapshot = $doc->snapshot();
            return new JsonResponse(
                $snapshot->data()
            );
        }
        
        return new JsonResponse(
            "This giftcard is already invalid"
        );
        
    }
}
