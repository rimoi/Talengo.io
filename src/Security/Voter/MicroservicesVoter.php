<?php

namespace App\Security\Voter;

use App\Entity\Microservice;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MicroservicesVoter extends Voter
{
    const MICROSERVICE_EDIT = "microservice_edit";
    const MICROSERVICE_DELETE = "microservice_delete";
    
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $service): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::MICROSERVICE_EDIT, self::MICROSERVICE_DELETE])
            && $service instanceof Microservice;
    }

    protected function voteOnAttribute(string $attribute, $miscroservice, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        //On autorise à un administrateur de modifier
        /*if ($this->security->isGranted("ROLE_ADMIN")) return true;*/

        //Verifie si une service a un auteur
        if (null === $miscroservice->getVendeur()) return false;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::MICROSERVICE_EDIT:
                // logic to determine if the user can EDIT
                return $this->canEdit($miscroservice, $user);
                break;
            case self::MICROSERVICE_DELETE:
                // logic to determine if the user can VIEW
                return $this->canDelete($miscroservice, $user);
                break;
        }

        return false;
    }

    private function canEdit(Microservice $miscroservice, User $user){
        return $user === $miscroservice->getVendeur();
    }

    private function canDelete(Microservice $miscroservice, User $user){
        if($this->security->isGranted("ROLE_ADMIN")) return true;
        return $user === $miscroservice->getVendeur();
    }
}
