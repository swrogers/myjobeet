<?php
// src/Ens/JobeetBundle/Controller/CategoryController.php

namespace Ens\JobeetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ens\JobeetBundle\Entity\Category;

/**
 * Category Controller
 * @Route("/category")
 */

class CategoryController extends Controller
{
    /**
     * Finds and shows category
     *
     * @Route("/{slug}", name="ens_category_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository('EnsJobeetBundle:Category')->findOneBySlug($slug);

        if(!$category)
        {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $category->setActiveJobs($em->getRepository('EnsJobeetBundle:Job')
            ->getActiveJobs($category->getId()));

        return array(
            'category' => $category,
        );
    }
}
