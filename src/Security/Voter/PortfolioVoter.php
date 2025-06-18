<?php

namespace App\Security\Voter;

use App\Entity\Competence;
use App\Entity\Realisation;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PortfolioVoter extends Voter
{
    const REALISATION_EDIT = "realisation_edit";
    const REALISATION_DELETE = "realisation_delete";
    
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $realisation): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::REALISATION_EDIT, self::REALISATION_DELETE])
            && $realisation instanceof Realisation;
    }

    protected function voteOnAttribute(string $attribute,  $realisation, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        //On autorise à un administrateur de modifier
        /*if ($this->security->isGranted("ROLE_ADMIN")) return true;*/

        //Verifie si une realisation a un auteur
        if (null === $realisation->getVendeur()) return false;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::REALISATION_EDIT:
                // logic to determine if the user can EDIT
                return $this->canEdit($realisation, $user);
                break;
            case self::REALISATION_DELETE:
                // logic to determine if the user can VIEW
                return $this->canDelete($realisation, $user);
                break;
        }

        return false;
    }

    private function canEdit(Realisation $realisation, User $user){
        return $user->getId() === $realisation->getVendeur()->getId();
    }

    private function canDelete(Realisation $realisation, User $user){
        if($this->security->isGranted("ROLE_ADMIN")) return true;
        return $user->getId() === $realisation->getVendeur()->getId();
    }
}
