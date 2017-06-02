<?php
namespace Prototype\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        $admin = $this->createQueryBuilder('a')
            ->where('a.username = :username OR a.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $admin) {
            $message = sprintf(
                'Unable to find an active admin object identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message);
        }

        return $admin;
    }

	public function refreshUser(UserInterface $admin)
    {
        $class = get_class($admin);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->find($admin->getId());
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }


    public function getUsersWithoutDeveloperRole()
    {
        $admins = $this->createQueryBuilder('a')
            ->where('a.roles not like :role')
            ->setParameter('role', '%ROLE_DEVELOPER%')
            ->getQuery()
            ->getResult();

        return $admins;
    }
}
