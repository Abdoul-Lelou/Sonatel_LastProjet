<?php

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\class_exists(\ContainerPFwJWLV\srcApp_KernelDevDebugContainer::class, false)) {
    // no-op
} elseif (!include __DIR__.'/ContainerPFwJWLV/srcApp_KernelDevDebugContainer.php') {
    touch(__DIR__.'/ContainerPFwJWLV.legacy');

    return;
}

if (!\class_exists(srcApp_KernelDevDebugContainer::class, false)) {
    \class_alias(\ContainerPFwJWLV\srcApp_KernelDevDebugContainer::class, srcApp_KernelDevDebugContainer::class, false);
}

return new \ContainerPFwJWLV\srcApp_KernelDevDebugContainer([
    'container.build_hash' => 'PFwJWLV',
    'container.build_id' => 'f8bb9c90',
    'container.build_time' => 1625860972,
], __DIR__.\DIRECTORY_SEPARATOR.'ContainerPFwJWLV');
