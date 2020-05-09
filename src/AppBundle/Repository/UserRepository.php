<?php

namespace AppBundle\Repository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public function getNbrTransporteur()
    {
        $qb=$this->getEntityManager()
            ->createQuery("select COUNT(u.id) AS NBR from AppBundle:User u where u.roles=?1")
        ->setParameters(array(1=>"a:1:{i:0;s:17:\"ROLE_TRANSPORTEUR\";}"));
        return $query = $qb->getResult();
    }
}
