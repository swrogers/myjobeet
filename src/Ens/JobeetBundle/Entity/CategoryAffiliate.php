<?php

namespace Ens\JobeetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryAffiliate
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ens\JobeetBundle\Entity\CategoryAffiliateRepository")
 */
class CategoryAffiliate
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="category_affiliates")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Affiliate", inversedBy="category_affiliates")
     * @ORM\JoinColumn(name="affiliate_id", referencedColumnName="id")
     */
    private $affiliate;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
