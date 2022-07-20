<?php

namespace App\Controller;

use Google\Cloud\Firestore\FirestoreClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
class giftcard_controller extends AbstractController
{
    /** 
     *@Route("/giftcard/create", methods={"POST"})
     */
    public function giftcreate(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $projectId = 'matejgrubisicgiftcard';

        $randomid = uniqid();

        $type = "DIGITAL";
        $time = getdate();
        $amount = random_int(10,100);
        $valid = true;
        $sales = 'asjdirjgeije4394839fdf';

        $firestore = new FirestoreClient([
            'projectId' => $projectId,
        ]);
        
        $document = $firestore->collection($_ENV['COLLECTION'])->newDocument();
        $document->create($data);

        

        return new JsonResponse(
            $document->snapshot()->data()
        );
        #works 
    }

    /** 
     *@Route("/giftcard/search/{id}", methods={"GET"})
     */
    public function giftget($id): JsonResponse
    {
        $projectId = 'matejgrubisicgiftcard';

        $firestore = new FirestoreClient([
            'projectId' => $projectId,
        ]);

        $doc = $firestore->collection($_ENV['COLLECTION'])->document($id);

        $snapshot = $doc->snapshot();
        #2315b7f8ed6b4e09b24f
        if ($snapshot->exists()) {
            printf('Document data:' . PHP_EOL);
        } else {
            printf('Document %s does not exist!' . PHP_EOL, $snapshot->id());
        }


        return new JsonResponse(
            [$snapshot->data()]
        );
    }
    /** 
     *@Route("/giftcard/redeem", methods={"POST"})
     */
    public function giftredeem(): JsonResponse
    {
        $projectId = 'matejgrubisicgiftcard';

        $firestore = new FirestoreClient([
            'projectId' => $projectId,
        ]);
        
        $doc = $firestore->collection($_ENV['COLLECTION'])->document('9c9bc67d1da246e3aa4e');
        $snapshot = $doc->snapshot();
        #d0a88fd2069542f1b297
        
        if ($snapshot->exists()) {
            $doc->update([
                ['path'=>'isValid', 'value'=>false]
            ]);
            
            $snapshot = $doc->snapshot();
            return new JsonResponse(
                $snapshot->data()    
            );
        } else {
            return new JsonResponse(
                ["This giftcard does not exist"]
            );
        }

        #$doc->delete();

        
    }
    /** 
     *@Route("/giftcard/invalidate", methods={"PATCH"})
     */
    public function giftinvalidate(): JsonResponse
    {
        $projectId = 'matejgrubisicgiftcard';
        #PATCH change bool

        $firestore = new FirestoreClient([
            'projectId' => $projectId,
        ]);
        
        $doc = $firestore->collection($_ENV['COLLECTION'])->document('06782d83da0b428097f0');
        #88f7d4fa794e499e812f
        $snapshot = $doc->snapshot();
        
        if ($snapshot->exists()) {
            $doc->update([
                ['path'=>'isValid', 'value'=>false]
            ]);
            $snapshot = $doc->snapshot();
            return new JsonResponse(
                $snapshot->data()
            );    
        } else {
            return new JsonResponse(
                "This giftcard does not exist"
            );
        }
        
    }
}
