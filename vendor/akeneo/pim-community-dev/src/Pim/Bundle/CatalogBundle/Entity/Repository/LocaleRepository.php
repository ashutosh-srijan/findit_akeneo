<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;

/**
 * Locale repository
 * Define a default sort order by code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be moved to Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository in 1.4
 */
class LocaleRepository extends ReferableEntityRepository implements LocaleRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = array('code' => 'ASC'), $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = array('code' => 'ASC'))
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedLocales()
    {
        $qb = $this->getActivatedLocalesQB();

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedLocaleCodes()
    {
        $qb = $this->getActivatedLocalesQB();
        $qb->select('l.code');

        $res = $qb->getQuery()->getScalarResult();

        $codes = [];
        foreach ($res as $row) {
            $codes[] = $row['code'];
        }

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function getActivatedLocalesQB()
    {
        $qb = $this->createQueryBuilder('l');
        $qb->where($qb->expr()->eq('l.activated', true))
            ->orderBy('l.code');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocalesQB()
    {
        $qb = $this->createQueryBuilder('l');
        $qb->orderBy('l.code');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('l');
        $rootAlias = $qb->getRootAlias();

        $qb->addSelect($rootAlias);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeletedLocalesForChannel(ChannelInterface $channel)
    {
        $currentLocaleIds = array_map(
            function ($locale) {
                return $locale->getId();
            },
            $channel->getLocales()->toArray()
        );

        $dql = <<<DQL
            SELECT l
            JOIN l.channels c
            WHERE c.id = :channel_id
              AND l.id NOT IN (:current_locale_ids)
DQL;

        $query = $this
            ->getEntityManager()
            ->createQuery($dql)
            ->setParameter(':channel_id', $channel->getId())
            ->setParameter(':current_locale_ids', implode(',', $currentLocaleIds));

        return $query->getResult();
    }
}
