<?php

namespace App\Controller;

use Google\Cloud\Firestore\FirestoreClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GiftcardController extends AbstractController
{
    const projectId = 'matejgrubisicgiftcard';
    /** 
     * @Route("/giftcard/create", methods={"POST"})
     */
    public function giftcreate(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $firestore = new FirestoreClient([
            'projectId' => self::projectId,
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
    public function giftget($id): JsonResponse
    {

        $firestore = new FirestoreClient([
            'projectId' => self::projectId,
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
    public function giftredeem($id): JsonResponse
    {
        #body money
        #validacija
        $firestore = new FirestoreClient([
            'projectId' => self::projectId,
        ]);

        $doc = $firestore->collection($_ENV['COLLECTION'])->document($id);
        $snapshot = $doc->snapshot();

        if (!$snapshot->exists()) {
            return new JsonResponse(
                ["This giftcard does not exist"]
            );
        } else {
            $doc->update([
                ['path' => 'isUsed', 'value' => true]
            ]);

            $snapshot = $doc->snapshot();
            return new JsonResponse(
                $snapshot->data()
            );
        }

        #$doc->delete();


    }
    /** 
     * @Route("/giftcard/invalidate/{id}", methods={"PATCH"})
     */
    public function giftinvalidate($id): JsonResponse
    {
        #PATCH change bool

        $firestore = new FirestoreClient([
            'projectId' => self::projectId,
        ]);

        $doc = $firestore->collection($_ENV['COLLECTION'])->document($id);

        $snapshot = $doc->snapshot();

        if (!$snapshot->exists()) {
            return new JsonResponse(
                "There is no giftcard"
            );
        } else{
            $doc->update([
                ['path' => 'isValid', 'value' => false]
            ]);
            $snapshot = $doc->snapshot();
            return new JsonResponse(
                $snapshot->data()
            );
        }
    }
}
