# SwissZip Bundle

provide functionalty for Swiss ZIP.

Datasource: https://swisspost.opendatasoft.com/explore/dataset/plz_verzeichnis_v2/information/?disjunctive.postleitzahl

- Import ZIP from local file or `swisspost.opendatasoft.com`
- suggest ZIP by name and ZIP

## Usage

### install
```
require whatwedo/swiss-zip
```


### create your entity

Create a new entity on your project. `use SwissZip` for implementing the `SwissZipInterface`. Add
custom properties for your needs.


`Entity/Location.php`

```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use whatwedo\SwissZip\Entity\SwissZipTrait;
use whatwedo\SwissZip\Entity\SwissZipInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 */
class Location implements SwissZipInterface
{
    use SwissZipTrait;
    
    // Add some custom properties
    // Add some project properties
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $whatwedoZip = false;

    public function isWhatwedoZip(): bool
    {
        return $this->whatwedoZip;
    }

    public function setWhatwedoZip(bool $whatwedoZip): self
    {
        $this->whatwedoZip = $whatwedoZip;
        return $this;
    }

}
```

### create your repository

`Repository/LocationRepository.php`


```php
namespace App\Repository;

use App\Entity\Location;
use Doctrine\Persistence\ManagerRegistry;
use whatwedo\SwissZip\Repository\SwissZipRepository;

/**
 * @method Location|null find($id, $lockMode = null, $lockVersion = null)
 * @method Location|null findOneBy(array $criteria, array $orderBy = null)
 * @method Location[]    findAll()
 * @method Location[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationRepository extends SwissZipRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }
}
```

### update your database

```
bin/console doctrine:schema:update --force
```
or
```
bin/console doctrine:migrations:migrate
```

### fill your table

```
bin/console whatwedo:swisszip:update
```

### create your event listener, if needed

`EventSubscriber/SwissZipSubscriber.php`

```php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use whatwedo\SwissZip\Event\Event;

class SwissZipSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            Event::DELETE => ['onDelete'],
            Event::CREATE => ['onCreate'],
            Event::UPDATE => ['onUpdate'],
            Event::PERSIST => ['onPersist'],
        ];
    }

public function onDelete(Event $event)    {
        if ($event->getEntity()->isWhatwedoZip())  {
            $event->getUpdateReport()->addMessage('we do here!');
            $event->setBlock(true);
        }
    }
    public function onCreate(Event $event)    {}
    public function onUpdate(Event $event)    {
        if ($event->getEntity()->getPostleitzahl() == '3011')  {
            $event->getUpdateReport()->addMessage('yes, here we do!');
            $event->getEntity()->setWhatwedoZip(true);
        }
    }
    public function onPersist(Event $event)   {}
}
```


