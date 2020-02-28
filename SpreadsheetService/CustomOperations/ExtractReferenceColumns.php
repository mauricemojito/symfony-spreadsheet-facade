<?php

namespace App\Service\SpreadsheetService\CustomOperations;

use App\Service\Contacts\Spreadsheet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExtractReferenceColumns implements Spreadsheet
{
    /**
     * @var
     */
    private $em;

    /**
     * @var
     */
    private $object;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * ExtractReferenceColumns constructor.
     *
     * @param $object
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     */
    public function __construct($object, EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->object = $object;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function handle()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $data = [
            'productPositions' => []
        ];

        foreach ($this->object as $key => $item) {

            $query = $this->em->createQueryBuilder()
                ->from('App\Entity\UserProductAttributeReference', 'par')
                ->where('par.cpcReference = :cpcReferenceId')
                ->setParameter('cpcReferenceId', $item['product_reference'])
                ->leftJoin('par.productAttribute', 'pa')
                ->addSelect('pa.id as productAttributeId')
                ->andWhere('par.user = :user')
                ->setParameter('user', $user)
                ->getQuery()->getOneOrNullResult();


                    $data['productPositions'][$key] = $query;
                    $data['productPositions'][$key]['quantity'] = $item['quantity'];
                    $data['productPositions'][$key]['product_reference'] = $item['product_reference'];
                if ($query) {
                    $data['productPositions'][$key]['error'] = false;
                } else {
                    $data['productPositions'][$key]['error'] = true;
                }
       }

       return $data;
    }
}