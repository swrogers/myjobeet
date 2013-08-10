<?php

namespace Ens\JobeetBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JobControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        // Make sure we're in the right controller
        $this->assertEquals('Ens\JobeetBundle\Controller\JobController::indexAction',
            $client->getRequest()->attributes->get('_controller'));
        
        // Make sure no expired jobs are listed
        $this->assertTrue($crawler->filter('.jobs td.position:contains("Expired")')->count() == 0);

        $kernel = static::createKernel();
        $kernel->boot();

        $max_jobs_on_homepage = $kernel->getContainer()->getParameter('max_jobs_on_homepage');

        // Make sure we don't go over the requested number of listed jobs
        $this->assertTrue( $crawler->filter('.category_programming tr')->count() <= $max_jobs_on_homepage );

        // Make sure a category link only shows if needed
        $this->assertTrue($crawler->filter('.category_design .more_jobs')->count() == 0);
        $this->assertTrue($crawler->filter('.category_programming .more_jobs')->count() == 1);

        // Make sure jobs are sorted by date
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        
        $query = $em->createQuery('SELECT j from EnsJobeetBundle:Job j LEFT JOIN j.category c WHERE c.slug = :slug AND j.expires_at > :date ORDER BY j.createdAt DESC');
        $query->setParameter('slug', 'programming');
        $query->setParameter('date', date('Y-m-d H:i:s', time()));
        $query->setMaxResults(1);
        $job = $query->getSingleResult();

        $this->assertTrue($crawler->filter('.category_programming tr')->first()->filter(sprintf('a[href*="/%d/"]', $job->getId()))->count() == 1);
    }
}
