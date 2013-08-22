I'll be using this project to teach myself Symfony2, using version 2.3.

I'll be following along with the tutorial from http://www.ens.ro/2012/03/21/jobeet-tutorial-with-symfony2/
and attempting to update it to the current version of Symfony as needed.

As an added degree of difficulty, I'm also forcing myself to learn git.

So this should be fun and exciting.

The current major changes between this version and the one from the ENS site are:
* I use annotations where possible, so the Doctrine definitions are in the Entities
* my version does not use the routing file, opting to go with SensioFrameworkExtraBundle
* hasFlash is no longer in Symfony 2.3, so the templates are updated to use app.session.flashbag
* I prefer to write queries using the QueryBuilder, not DQL
* Doctrine 2 comes with the ability to use pagination, so I've opted to go that route instead of using an offset. There is not really much difference either way, however.
* Annotations used for entity/form validation as opposed to config files
* The logout routes are defined at the app level and not the bundle level as I'm using annotations for routing.
* The security provider for the User class needed to be defined as EnsJobeetBundle:User as opposed to the full class name in security.yml
