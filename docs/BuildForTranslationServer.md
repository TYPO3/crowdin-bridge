# Build for Translation Server

The main task of this whole package is downloading translations from Crowdin and
preper those for the proper format required by the translation server.

## Github Workflows

All processes are automated and run via GitHub Actions.

### 1) Build

The workflow `Crowdin Build` triggers a build on Crowdin.
Builds are required to package translation on Crowdin as only packaged translations are available for downloading.

## 2.) Extract and Processing

The following steps are taken:

1. Generate local configuration file `crowdin:setup`
1. Download & process all translations
   2. From Extensions: `crowdin:meta:extractExt`
   3. From Core: `crowdin:extract:core '*'`
2. Create status
   3. Single status for each extension `crowdin:meta:status.export`
   4. Total status `crowdin:management:status --export=1`
5. Copy (rsync) all files to translation server

### Configuration

The following secrets are required:

- `CROWDIN_ACCESS_TOKEN`: Access token for Crowdin Account
- `SCP_HOST`: SSH Host info
- `SCP_USERNAME`: SSH user info
- `SCP_PORT`: SSH port info
- `SSH_PRIVATE_KEY`: SSH private key
