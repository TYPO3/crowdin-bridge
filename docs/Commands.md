# Commands

The following commands are available:

## `management:projectList`

Running this command is required by most of the other commands and generates a
local configuration file.

`php app.php management:projectList`

## `management:status`

Generate a stauts overview.

`php app.php management:status`

## `build`

This command triggers the build process of a project at Crowdin. Updated
translations are only after a build process available for downloads.

`php app.php build`

**Arguments:**

* `project` *required*: Project identifier

## `extract:core`

Trigger the download of core translations.

**Arguments:**

* `language` *required*: Languages to download, use `'*'` for all languages of
  this project

`php app.php extract:core '*'`

## `extract:ext`

Download of extension translations. Always all languages are downloaded.

**Arguments:**

* `project` *required*: Project identifier

`php app.php extract:ext typo3-extension-news`

## `status`

Show status of a project

**Arguments:**

* `project` *required*: Project identifier

`php app.php status typo3-extension-news`

## `status.export`

Export simplified status of a project as json file to the same directory as
translations.

**Arguments:**

* `project` *required*: Project identifier

`php app.php status.export typo3-extension-news`

## Meta commands

The following meta commands group the subcommands together and makes it easier
for automatic builds:

* `meta:build`: Trigger build all projects
* `meta:extractExt`: Download translations of all extensions
* `meta:status.export`: Export status of all extensions
