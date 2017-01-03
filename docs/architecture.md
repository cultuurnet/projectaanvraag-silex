# Architecture

The application uses a command / event based architecture. The system is implemented using [Simplebus](https://github.com/SimpleBus)
 
## Controllers
 The controllers main functionality is to accept HTTP requests and initialize the correct bussiness service. Controllers should contain no business logic.
 
 Controllers can call the business logic using:
 - the command bus
 - provided services (example [ProjectService](src/Project/ProjectService.php))
 
## Services and storages
 Services and storages are currently used to load certain data (from API or local storage). The main class always comes with an interface. (Exampes: [IntegrationTypeStorage](src/IntegrationType/IntegrationType.php) and [ProjectService](src/Project/ProjectService.php

## Entities
 The entities are used to load and save data to the database using [ORM](http://docs.doctrine-project.org/en/latest/). You can use the entity manager to load entities.
 Example of loading and saving:
 ```
 $coupon = $this->entityManager->getRepository('ProjectAanvraag:Coupon')->find($activateProject->getCouponToUse());
 $coupon->setUsed(true);
 $this->entityManager->persist($coupon);
 $this->entityManager->flush(); 
 ```
 
 ## Commands
 To implement a command, following steps are required:
  - Create your command class (example [RequestActivation](src/Project/Command/RequestActivation.php))
  - Create a handler class. The handler class should contain a handle method that receives the type of command it can handle. (example [RequestActivation](src/Project/CommandHandler/RequestActivationCommandHandler.php))
  - Add your handler in the [configuration](app/config/messagebus.yml)). You can add dependencies for your handler as arguments.
  ```
  handlers:
      create_project_handler:
          command: CultuurNet\ProjectAanvraag\Project\Command\CreateProject
          class: \CultuurNet\ProjectAanvraag\Project\CommandHandler\CreateProjectCommandHandler
          arguments:
              - event_bus
              - orm.em
              - culturefeed_test
              - culturefeed
              - uitid_user_session_data_complete
  ```              
 
 ## Events
 To implement an event and related event listers. Following steps are required:
   - Create your event class (example [ProjectActivated](src/Project/Event/ProjectActivated.php))
     - An event can be thrown async or sync. If you want it to be consumed by rabbitmq, implement the AsynchronousMessageInterface interface.
     - By implementing the `DelayableMessageInterface` interface, an event can be thrown with a delay.
   - Create an event listener. (example [ProjectActivatedEventListener](src/Project/EventListener/ProjectActivatedEventListener.php)).
     - An event listener should always contain a handle method that receives the event.