<div>
    <!-- Header Section -->
    <x-header :heading="$heading" :section="$section" />

    <!-- Content Section -->
    <x-folder-content-section :title="$heading" :selectedIcon="$this->icon" :color="$this->color" :icons="$this->icons" :usedIcons="$this->usedIcons"
        submitAction="createFolder" autofocus />
</div>
