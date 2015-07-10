<?php

namespace Optisoop\Bundle\AdminBundle\Service\DataTables;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Class JsonList
 *
 * Returns a list in JSON format.
 */
class JsonList
{
    /** @var integer */
    protected $offset;

    /** @var integer */
    protected $limit;

    /** @var integer */
    protected $sortColumn;

    /** @var string */
    protected $sortDirection;

    /** @var string */
    protected $search;

    /** @var integer */
    protected $echo;

    /** @var ObjectRepository */
    protected $repository;

    /** @var integer */
    protected $entityId=null;
    
    /** @var integer */
    protected $transactionId=null;
    
    /** @var integer */
    protected $projectId=null;

    /** @var integer */
    protected $projectIds=null;
    
    /** @var integer */
    protected $newsletter=false;
    
    /**
     * Constructor
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->offset = intval($request->get('iDisplayStart'));
        $this->limit = intval($request->get('iDisplayLength'));
        $this->sortColumn = intval($request->get('iSortCol_0'));
        $this->sortDirection = $request->get('sSortDir_0');
        $this->search = $request->get('sSearch');
        $this->echo = intval($request->get('sEcho'));

        return $this;
    }

    /**
     * Set the repository
     *
     * @param ObjectRepository $repository
     */
    public function setRepository(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Set the entity ID
     *
     * @param integer $id
     */
    public function setEntityId($id)
    {
        $this->entityId = $id;
    }
    
    /**
     * Set the transaction ID
     *
     * @param integer $id
     */
    public function setTransactionId($id)
    {
        $this->transactionId = $id;
    }
    
    /**
     * Set the project ID
     *
     * @param integer $id
     */
    public function setProjectId($id)
    {
        $this->projectId = $id;
    }
    
    /**
     * Set the projectIds
     *
     * @param integer $projectIds
     */
    public function setProjectIds($projectIds)
    {
        $this->projectIds = $projectIds;
    }
    
    /**
     * Set the project ID
     *
     * @param integer $newsletter
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * Get the list
     *
     * @return array
     */
    public function get()
    {
        $totalEntities = $this->repository->countTotal($this->entityId);

        if(!is_null($this->projectIds)){
            $entities = $this->repository->findAllForDataTablesByProjects($this->search, $this->sortColumn, $this->sortDirection, $this->projectIds);
        }elseif(!is_null($this->transactionId)){
            $entities = $this->repository->findAllForDataTablesByTransaction($this->search, $this->sortColumn, $this->sortDirection, $this->transactionId);
        }elseif(!is_null($this->projectId)){
            $entities = $this->repository->findAllForDataTablesByProject($this->search, $this->sortColumn, $this->sortDirection, $this->projectId);
        }else{
            $entities = $this->repository->findAllForDataTables($this->search, $this->sortColumn, $this->sortDirection, $this->entityId);
        }
        
        if($this->newsletter)
            $entities = $this->repository->findNewsletterSubscription($this->search, $this->sortColumn, $this->sortDirection);

        $totalFilteredEntities = count($entities->getScalarResult());

        // paginate
        $entities->setFirstResult($this->offset)
            ->setMaxResults($this->limit);

        $data = $entities->getResult();


        return array(
            'iTotalRecords'         => $totalEntities,
            'iTotalDisplayRecords'  => $totalFilteredEntities,
            'sEcho'                 => $this->echo,
            'aaData'                => $data
        );
    }
    
}