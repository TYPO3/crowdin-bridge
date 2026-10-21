# TYPO3 Core versions

The TYPO3 Core versions are maintained in [CoreInformation.php](../src/Info/CoreInformation.php). The file is
self-explanatory.

## New Core branches

When a new Core branch is created (after releasing a new LTS version), it has to be added to `VERSIONS` and
`BRANCH_MAPPING` constants. Remember to synchronize the Core branch in Crowdin itself. Also, think of new
system extensions, which must be added to the `CORE_EXTENSIONS` constant to be extracted and available on the
translation server.

## Outdated Core branches

Outdated TYPO3 branches (like ELTS or older) can be removed from `VERSIONS` and `BRANCH_MAPPING` constants. There
might also be superfluous system extension which can be deleted from `CORE_EXTENSIONS` constant.

The already extracted and synchronized translations for these branches are kept on the translation server and not
deleted (which is what we want to have).
