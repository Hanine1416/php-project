<?php

namespace UserBundle\Repository;

use UserBundle\Entity\ResetPasswordRequest;

/**
 * ResetPasswordRequestRepository
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ResetPasswordRequestRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $token
     * @param $maxValidTime
     * @return ResetPasswordRequest|null
     */
    public function findValidToken($token, $maxValidTime):?ResetPasswordRequest
    {
        $result = $this->createQueryBuilder('rpr')
            ->where('rpr.token = :token')
            ->andWhere('rpr.enabled = 1')
            ->andWhere('rpr.requestedAt > :validDate')
            ->setParameters(['token' => $token, 'validDate' => $maxValidTime])
            ->getQuery()->getResult();
        return count($result) > 0 ? $result[0] : null;
    }
}
