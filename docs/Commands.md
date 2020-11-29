## Commands

The following commands are available:

### `crowdin:management:projectList`

Running this command is required by most of the other commands and generates a local configuration file.

`php app.php crowdin:management:projectList`

### `crowdin:management:status`

Generate a stauts overview.

`php app.php crowdin:management:status`

### `crowdin:build`

This command triggers the build process of a project at Crowdin.
Updated translations are only after a build process available for downloads.

`php app.php crowdin:build`

**Arguments**

- `project` *required*: Project identifier

### `crowdin:extract:core`

Trigger the download of core translations.

**Arguments**

- `language` *required*: Languages to download, use `'*'` for all languages of this project

`php app.php crowdin:extract:core '*'`

### `crowdin:extract:ext`

Download of extension translations. Always all languages are downloaded.

**Arguments**

- `project` *required*: Project identifier

`php app.php crowdin:extract:ext typo3-extension-news`

### `crowdin:status`

Show status of a project

**Arguments**

- `project` *required*: Project identifier

`php app.php crowdin:status typo3-extension-news`

### `crowdin:status.export`

Export simplified status of a project as json file to the same directory as translations.

**Arguments**

- `project` *required*: Project identifier

`php app.php crowdin:status.export typo3-extension-news`

### Meta commands

The following meta commands group the subcommands together and makes it easier for automatic builds:

- `crowdin:meta:build`: Trigger build all projects
- `crowdin:meta:extractExt`: Download translations of all extensions
- `crowdin:meta:status.export`: Export status of all extensions



