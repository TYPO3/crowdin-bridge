# Build for Translation Server

The main task of this whole package is downloading translations from Crowdin and
prepare those for the proper format required by the translation server.

## Github Workflows

All processes are automated and run via GitHub Actions.

### Build

The workflow `Crowdin Build` triggers a build on Crowdin.

Builds are required to package translation on Crowdin as only packed
translations are available for downloading.

### Extract and Processing

The following steps are taken:

1. Generate local configuration file `crowdin:setup`
2. Download & process all translations
   1. From Extensions: `crowdin:meta:extractExt`
   2. From Core: `crowdin:extract:core '*'`
3. Create status
   1. Single status for each extension `crowdin:meta:status.export`
   2. Total status `crowdin:management:status --export=1`
4. Copy (rsync) all files to translation server

### Configuration

The following secrets are required:

- `CROWDIN_ACCESS_TOKEN`: Access token for Crowdin Account
- `SCP_HOST`: SSH Host info
- `SCP_USERNAME`: SSH user info
- `SCP_PORT`: SSH port info
- `SSH_PRIVATE_KEY`: SSH private key
