# CHANGELOG

## 2.0.0

- Removing Java dependency (no longer needed)
- No longer saving and uploading the sarif file result
- Now the action is able to comment back on PR with the results of the scan
- Using the new action from checkmarx (using cxflow) in place of the almost deprecated one (which uses the cli)
- REQUIRES a shell runner or a runner that works well with DIND

## 1.1.0

- Using actions/checkout@v3 instead of v2

## 1.0.0

- First release
