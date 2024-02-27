#!/usr/bin/env bash
# The shell script is used to Run SSM Command with parameters for against a SSM Document
# To select the instance it needs to be runned passing the Environment tag and Description to it
set -o errexit
set -o pipefail
set -o nounset

if [ "$#" -lt 3 ]; then
    echo "Incorrect number of arguments supplied. Usage:"
    echo "  Instance environment filter (Eg: dev, qa, prod): [*.<env>.*.icopy_EC2_Instance*]"
    echo "  Playbook Name"
    echo "  Parameters"
    exit 2
fi

TAG_ENV="${1}"
PLAYBOOK="${2}"
PARAMETERS="${3}"

echo "Running Ansible Playbook: ${PLAYBOOK} - Parameters: ${PARAMETERS} - Filter => Tag Name *.${TAG_ENV}.*.icopy_EC2_Instance*"

INSTANCES=($(aws --region eu-west-1 ec2 describe-instances --filters "Name=tag:Name,Values=*.${TAG_ENV}.*.icopy_EC2_Instance*" "Name=instance-state-name,Values=running" | jq -r '.Reservations[] | .Instances[] | .InstanceId'))
for i in "${INSTANCES[@]}"; do
    echo Updating Instance-ID: $i
    sh_command_id=$(aws ssm send-command \
      --instance-ids "${i}" \
      --document-name "AWS-ApplyAnsiblePlaybooks" \
      --document-version "1" \
      --parameters "${PARAMETERS}" \
      --output text \
      --query "Command.CommandId")
    command_status="Pending"
    while [ "$command_status" == "Pending" ] || [ "$command_status" == "InProgress" ]; do
      sleep 5
      command_status=$(aws ssm list-commands \
        --command-id "${sh_command_id}" \
        --output text \
        --query "Commands[].{Status:Status}")
    done
    echo "Command ${sh_command_id} Finished with ${command_status}"
    echo "The Output of the commands run are below:"
    aws ssm list-command-invocations \
    --command-id "${sh_command_id}" \
    --details \
    --output text \
    --query "CommandInvocations[].CommandPlugins[].{Output:Output}"
    echo "The Command started at:"
    aws ssm list-command-invocations \
    --command-id "${sh_command_id}" \
    --details \
    --output text \
    --query "CommandInvocations[].CommandPlugins[].{ResponseStartDateTime:ResponseStartDateTime}"
    echo "The Command Finished at:"
    aws ssm list-command-invocations \
    --command-id "${sh_command_id}" \
    --details \
    --output text \
    --query "CommandInvocations[].CommandPlugins[].{ResponseFinishDateTime:ResponseFinishDateTime}"
    ssm_fail=$(aws ssm list-command-invocations \
      --command-id "${sh_command_id}" \
      --details \
      --output text \
      --query "CommandInvocations[].CommandPlugins[].{Output:Output}" \
      | { grep -c -- -ERROR- || true;})
    if [ "$ssm_fail" = 0 ]; then
      echo "SSM Command Ran Successfully"
    else
      echo "There was an error in the SSM Command. Please debug it."
      exit 1
    fi
done
