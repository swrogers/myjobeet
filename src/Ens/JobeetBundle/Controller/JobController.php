<?php

namespace Ens\JobeetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ens\JobeetBundle\Entity\Job;
use Ens\JobeetBundle\Form\JobType;

/**
 * Job controller.
 *
 * @Route("/")
 */
class JobController extends Controller
{

    /**
     * Lists all Job entities.
     *
     * @Route("/", name="ens_job")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('EnsJobeetBundle:Category')->getWithJobs();

        foreach( $categories as $category )
        {
            $category->setActiveJobs(
                $em->getRepository('EnsJobeetBundle:Job')
                    ->getActiveJobs($category->getId(), $this->container->getParameter('max_jobs_on_homepage'))
                );

            $category->setMoreJobs(
                $em->getRepository('EnsJobeetBundle:Job')
                    ->countActiveJobs($category->getId()) - $this->container->getParameter('max_jobs_on_homepage'));
        }

        return array(
            'categories' => $categories,
        );
    }
    /**
     * Creates a new Job entity.
     *
     * @Route("/", name="ens_job_create")
     * @Method("POST")
     * @Template("EnsJobeetBundle:Job:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Job();
        $form = $this->createForm(new JobType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ens_job_preview', array(
                'token' => $entity->getToken(),
                'company' => $entity->getCompany(),
                'position' => $entity->getPosition(),
                'location' => $entity->getLocation(),
            )));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Job entity.
     *
     * @Route("/new", name="ens_job_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Job();
        $entity->setType('full-time');
        $form   = $this->createForm(new JobType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Job entity.
     *
     * @Route("/job/{company}/{location}/{id}/{position}", name="ens_job_show", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EnsJobeetBundle:Job')->getActiveJob($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        // Save the last three jobs viewed so the user can easily get back to them
        $session = $this->getRequest()->getSession();
    
        $jobs = $session->get('job_history', array());

        $job = array('id' => $entity->getId(),
                'position' => $entity->getPosition(),
                'company' => $entity->getCompany(),
                'companyslug' => $entity->getCompanySlug(),
                'locationslug' => $entity->getLocationSlug(),
                'positionslug' => $entity->getPositionSlug(),
        );

        if(!in_array($job, $jobs))
        {
            // add the current job at the beginning of the jobs array
            array_unshift($jobs, $job);

            // set job history session of only 3 items
            $session->set('job_history', array_slice($jobs, 0, 3));
        }

        $deleteForm = $this->createDeleteForm($entity->getToken());

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Job entity.
     *
     * @Route("/job/{token}/edit", name="ens_job_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($token)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EnsJobeetBundle:Job')->findOneByToken($token);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        if($entity->getIsActivated())
        {
            throw $this->createNotFoundException('Job is activated and cannot be edited.');
        }

        $editForm = $this->createForm(new JobType(), $entity);
        $deleteForm = $this->createDeleteForm($token);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Job entity.
     *
     * @Route("/job/{token}/update", name="ens_job_update")
     * @Method("POST")
     * @Template("EnsJobeetBundle:Job:edit.html.twig")
     */
    public function updateAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EnsJobeetBundle:Job')->findOneByToken($token);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $deleteForm = $this->createDeleteForm($token);
        $editForm = $this->createForm(new JobType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('ens_job_preview', array(
                'token' => $entity->getToken(),
                'company' => $entity->getCompanySlug(),
                'location' => $entity->getLocationSlug(),
                'position' => $entity->getPositionSlug()
            )));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Previews a Job entity
     * Similar as showAction but uses token id
     *
     * @Route("/job/{company}/{location}/{token}/{position}", name="ens_job_preview", requirements={"token" = "\w+"})
     * @Template("EnsJobeetBundle:Job:show.html.twig")
     */
    public function previewAction(Request $request, $token)
    {
       $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EnsJobeetBundle:Job')->findOneByToken($token);

        if(!$entity) 
        {
            throw $this->createNotFoundException('Unable to find Job entity.');
        } 

        $deleteForm = $this->createDeleteForm($entity->getToken());
        $publishForm = $this->createPublishForm($entity->getToken());
        $extendForm = $this->createExtendForm($entity->getToken());

        return array(
            'entity' => $entity,
            'id' => $entity->getId(),
            'company' => $entity->getCompanySlug(),
            'location' => $entity->getLocationSlug(),
            'position' => $entity->getPositionSlug(),
            'delete_form' => $deleteForm->createView(),
            'publish_form' => $publishForm->createView(),
            'extend_form' => $extendForm->createView(),
        );
        
    }

    /**
     * Publishes a Job
     * @Route("/job/{token}/publish", name="ens_job_publish")
     * @Method("POST")
     * @Template()
     */
    public function publishAction(Request $request, $token)
    {
        $form = $this->createPublishForm($token);

        $form->bind($request);

        if($form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('EnsJobeetBundle:Job')->findOneByToken($token);

            if(!$entity)
            {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }
        
            $entity->publish();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('notice', 'Your job is now online for 30 days.');
        }

        return $this->redirect(
            $this->generateUrl('ens_job_preview', array(
                'company' => $entity->getCompanySlug(),
                'location' => $entity->getLocationSlug(),
                'token' => $entity->getToken(),
                'position' => $entity->getPositionSlug(),
        )));
    }
    
    /**
     * Deletes a Job entity.
     *
     * @Route("/{token}/delete", name="ens_job_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $token)
    {
        $form = $this->createDeleteForm($token);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('EnsJobeetBundle:Job')->findOneByToken($token);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('ens_job'));
    }

    /**
     * Extends the expiration date
     *
     * @Route("/job/{token}/extend", name="ens_job_extend")
     * @Method("POST")
     */
    public function extendAction(Request $request, $token)
    {
        $form = $this->createExtendForm($token);
        $form->bind($request);

        if($form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('EnsJobeetBundle:Job')->findOneByToken($token);
    
            if(!$entity)
            {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            if(!$entity->extend())
            {
                throw $this->createNotFoundException('Unable to extend the job.');
            }

            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('notice',
                    sprintf('Your job validity has been extended until %s.',
                        $entity->getExpiresAt()->format('m/d/Y')));
        }

        return $this->redirect($this->generateUrl('ens_job_preview', array(
            'company' => $entity->getCompanySlug(),
            'location' => $entity->getLocationSlug(),
            'token' => $entity->getToken(),
            'position' => $entity->getPositionSlug(),
        )));
    }


    /**
     * Creates a form to delete a Job entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($token)
    {
        return $this->createFormBuilder(array('token' => $token))
            ->add('token', 'hidden')
            ->getForm()
        ;
    }

    /**
     * Creates a form to publish a Job entity
     */
    private function createPublishForm($token)
    {
        return $this->createFormBuilder(array('token' => $token))
            ->add('token', 'hidden')
            ->getForm();
    }

    /**
     * Creates a form for extending a Job entity
     */
    private function createExtendForm($token)
    {
        return $this->createFormbuilder(array('token' => $token))
            ->add('token', 'hidden')
            ->getForm();
    }
}
