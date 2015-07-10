<?php

namespace Core\Bundle\AdminBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Core\Bundle\CoreBundle\Entity\Actor;

/**
 * Class AdminManager
 */
class AdminManager
{
    private $entityManager;
    
    private $securityContext;
    
    private $parameters;

    private $container;


    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, $securityContext, array $parameters, $container)
    {
        $this->entityManager = $entityManager;
        $this->securityContext = $securityContext;
        $this->parameters = $parameters['parameters'];
        $this->container = $container;
    }

    /**
     * Sort entities from the given IDs
     *
     * @param string $entityName
     * @param string $values
     */
    public function sort($entityName, $values)
    {
        $values = json_decode($values);

        for ($i=0; $i<count($values); $i++) {
            $this->entityManager
                ->getRepository($entityName)
                ->createQueryBuilder('e')
                ->update()
                ->set('e.order', $i)
                ->where('e.id = :id')
                ->setParameter('id', $values[$i]->id)
                ->getQuery()
                ->execute();
        }
    }

    /**
     * Sets an entity as filtrable
     *
     * @param string $entityName
     * @param int    $id
     *
     * @throws NotFoundHttpException
     * @return boolean
     */
    public function toggleFiltrable($entityName, $id)
    {
        $entity = $this->entityManager->getRepository($entityName)->find($id);

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        $entity->toggleFiltrable();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity->isFiltrable();
    }
    
    public function uploadWebImage($image, $entity)
    {
        $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
        $imageName = sha1(uniqid(mt_rand(), true)) . '.' . $extension;

        if ($image->move($this->getAbsolutePathWeb($entity->getId()), $imageName)) {
            return $imageName;
        }
        else {
            return null;
        }
    }
    
    public function uploadProfileImage($image, $entity)
    {
        $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
        $imageName = sha1(uniqid(mt_rand(), true)) . '.' . $extension;

        if ($image->move($this->getAbsolutePathProfile($entity->getId()), $imageName)) {
            return $imageName;
        }
        else {
            return null;
        }
    }
    
    public function getAbsolutePathProfile($id) {
        return $this->getWebPath() .  $this->parameters['upload_directory'] . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'profile'.  DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR;
    }
    
    public function getAbsolutePathWeb($id) {
        return $this->getWebPath() .  $this->parameters['upload_directory'] . DIRECTORY_SEPARATOR . 'images'. DIRECTORY_SEPARATOR . 'web'.  DIRECTORY_SEPARATOR . $id .DIRECTORY_SEPARATOR;
    }

    public function getWebPath() {
        return __DIR__ . '/../../../../../web/';
    }
    
     /**
    * Returns the image path of user actor
    *
    */
    public function getProfileImage($actor=null)
    {

        if (is_null($actor)) {
                $actor = $this->container->get('security.context')->getToken()->getUser();
        }

        if ($actor instanceof Actor && $actor->getImage() instanceof Image) {
            $profileImage = '/uploads/images/profile/'.$actor->getId().'/'.$actor->getImage()->getPath();
        } else {
            $profileImage = $this->getDefaultImageProfile();
        }
 
        return  $profileImage;
    }
    
    public function getDefaultImageProfile()
    {
        return '/bundles/admin/img/default_profile.png';
    }
    
   
    
}