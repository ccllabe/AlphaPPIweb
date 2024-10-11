#!/bin/sh

# Replace with the actual Python environment path where AlphaPulldown is installed
python_env="/path/to/alphapulldown_env/bin/python"

# Replace with the actual job.py path
job_script="/path/to/job.py"

while true
do
    $python_env $job_script
    sleep 3600
done