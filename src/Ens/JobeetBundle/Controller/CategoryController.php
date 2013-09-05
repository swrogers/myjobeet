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
     * @Route("/{slug}/index.{_format}", name="ens_category_show_format")
     * @Route("/{slug}/{page}", name="ens_category_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($slug, $page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository('EnsJobeetBundle:Category')->findOneBySlug($slug);

        if(!$category)
        {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $total_jobs = $em->getRepository('EnsJobeetBundle:Job')->countActiveJobs($category->getId());
        $jobs_per_page = $this->container->getParameter('max_jobs_on_category');
        $last_page = ceil( $total_jobs / $jobs_per_page );
        $previous_page = $page > 1 ? $page-1 : 1;
        $next_page = $page < $last_page ? $page+1 : $last_page;

        $category->setActiveJobs($em->getRepository('EnsJobeetBundle:Job')
            ->getActiveJobs($category->getId(),
                    $jobs_per_page,
                    $page));
        
        $context = array(
            'category' => $category,
            'last_page' => $last_page,
            'previous_page' => $previous_page,
            'current_page' => $page,
            'next_page' => $next_page,
            'total_jobs' => $total_jobs,
        );
    
        $format = $this->getRequest()->getRequestFormat();

        if('html' !== $format)
        {
            $feedId = sha1($this->get('router')->generate('ens_category_show_format', array('slug' => $category->getSlug(), '_format' => 'atom'), true));

            $latest_job = $em->getRepository('EnsJobeetBundle:Job')->getLatestPost($category->getId());

            $context['feedId'] = $feedId;            
            $context['latest_job'] = $latest_job;

            return $this->render('EnsJobeetBundle:Category:show.'.$format.'.twig', $context);
        }
        
        return $context;
    }
}
