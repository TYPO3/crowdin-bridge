# Project `CrowdinBridge`

**Still in beta!**

![](https://github.com/typo3/crowdin-bridge/workflows/Crowdin%20Build/badge.svg)
![](https://github.com/typo3/crowdin-bridge/workflows/Crowdin%20Extract/badge.svg)

This project is the bridge between Crowdin and TYPO3.
The purpose is to download translations from Crowdin and copy those to the translation server.

## Setup

Run `composer install` to install the package.
After that, copy the configuration example file `examples/configuration.json` one level up and setup the directories properly.

## Commands

The following commands are available:

### `crowdin:build`

This command triggers the build process of a project at Crowdin.
Updated translations are only after a build process available for downloads.

**Command**

`php app.php crowdin:build`

**Arguments**

- `project` *required*: Project identifier
- `branch` *optional*: Branch for building
- `async` *optional*: Set to true for not waiting for any feedback, useful if project is big


### `crowdin:extract:core`

Trigger the download of core translations. Always all branches are downloaded.

**Arguments**

- `language` *required*: Languages to download, use `'*'` for all languages of this project

**Example**

`php app.php crowdin:extract:core '*'`

### `crowdin:extract:ext`

Download of extension translations. Always all languages are downloaded.

**Arguments**

- `project` *required*: Project identifier
- `language` *required*: Languages to download, use `'*'` for all languages of this project

**Example**

`php app.php crowdin:extract:ext typo3-extension-news de`

### `crowdin:api:add`

Add new project to configuration

**Arguments**

- `project` *required*: Project identifier
- `key` *required*: API key
- `extensionKey` *required*: Extension key

**Command**

`php app.php crowdin:api:add typo3-extension-rxshariff 12345678 rx_shariff`

### `crowdin:status`

Show status of a project

**Arguments**

- `project` *required*: Project identifier

**Command**

`php app.php crowdin:status typo3-extension-news`

### `crowdin:status.export`

Export simplified status of a project as json file to the same directory as translations.

**Arguments**

- `project` *required*: Project identifier

**Command**

`php app.php crowdin:status.export typo3-extension-news`

### Meta commands

The following meta commands group the subcommands together and makes it easier for automatic builds:

- `crowdin:meta:build`: Trigger build all projects
- `crowdin:meta:extractExt`: Download translations of all extensions
- `crowdin:meta:status.export`: Export status of all extensions

## Github Actions

The export runs automatically and uses GitHub Actions. The following workflows are available

### Workflows

- *Crowdin Build*: The build triggers the build process of all Crowdin projects.
- *Crowdin Extract*: Trigger export of translations of core + extensions and the translation status

### Secrets

The following secrets are required:

- `CONFIGURATION_URL`: URL to the configuration.json
- `SCP_HOST`: SSH Host info
- `SCP_USERNAME`: SSH user info
- `SCP_PORT`: SSH port info
- `SSH_PRIVATE_KEY`: SSH private key
- `CROWDIN_USER_KEY`: Access key for Crowdin Account




