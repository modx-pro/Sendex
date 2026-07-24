<?php

class modUser extends xPDOSimpleObject
{
    /** @var bool */
    public $active = true;

    /**
     * @param string $alias
     *
     * @return modUserProfile|null
     */
    public function getOne($alias)
    {
        if ($alias === 'Profile' && $this->xpdo instanceof FakeModX) {
            $id = (int) $this->get('id');
            if (isset($this->xpdo->userProfiles[$id])) {
                return $this->xpdo->userProfiles[$id];
            }
            if (isset($this->xpdo->profiles[$id])) {
                $profile = new modUserProfile($this->xpdo);
                $profile->set('email', $this->xpdo->profiles[$id]);
                $profile->set('blocked', false);
                $profile->set('internalKey', $id);

                return $profile;
            }
        }

        return null;
    }
}
