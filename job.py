# -*- coding: utf-8 -*-

# initial tool
import os
import sys
import tempfile
from pathlib import Path
import shutil
import time
from datetime import datetime, timedelta
import json
# biopython
from Bio import SeqIO

# initial parameter
current_dir = os.path.dirname(os.path.abspath(__file__))
os.chdir(current_dir)
jobs_path = "./jobs"
user_data_path = "/path/to/user_data" # Replace with the actual user data path
python_env = "/path/to/alphapulldown_env/bin/python" # Replace with the actual Python environment path where AlphaPulldown is installed
alphapulldown_path = "/path/to/alphapulldown_env/bin/" # Replace with the actual AlphaPulldown path
seq_database = "/path/to/alphafold_database" # Replace with the actual AlphaFold database path
run_pae_img = "/path/to/alpha-analysis.sif" # Replace with the actual AlphaPulldown analysis image path
pymol_script_path = "/path/to/pymol_align_script.py" # Replace with the actual PyMol script path

# Confirm the status of pending tasks and perform task calculations
def cron_computing():
    try:
        is_run, job = cron_state()
        if(is_run):
            print(job)
            job_handle(job)
        sys.exit(0)
    except SystemExit:
        pass

# Confirm the status of pending tasks and return the task that should be executed.
def cron_state():
    global jobs_path
    r_log_path = os.path.join(jobs_path, "running.json")
    # process is running now
    if(os.path.isfile(r_log_path)):
        return False, None
    # no job need to run
    jobs = os.listdir(jobs_path)
    if(len(jobs)<1):
        return False, None
    jobs.sort()
    return True, jobs[0]

# Perform task
def job_handle(job):
    global jobs_path, user_data_path
    job_path = os.path.join(jobs_path, job)
    r_log_path = os.path.join(jobs_path, "running.json")
    Path(r_log_path).touch()
    user_id_path = os.path.join(user_data_path, job)
    state_log_path = os.path.join(user_id_path, "state.json")
    Path(state_log_path).touch(mode=0o644)
    state_content = {"step_1":None,"step_2":None,"step_3":None,"step_4":None,"end":None}
    state_log_jswrite(state_content, "step_1", state_log_path)
    seq_to_msa_tf(user_id_path)
    state_log_jswrite(state_content, "step_2", state_log_path)
    msa_tf_to_predict_struct(user_id_path)
    state_log_jswrite(state_content, "step_3", state_log_path)
    predict_struct_get_good_pae(user_id_path)
    state_log_jswrite(state_content, "step_4", state_log_path)
    pymol_get_align_pdb(user_id_path)
    state_log_jswrite(state_content, "end", state_log_path)
    os.system(f"rm -rf {job_path}")
    os.system(f"rm {r_log_path}")

# Write state content (for Web progress bar check)
def state_log_jswrite(content, key, save_path):
    content[key] = time.time()
    temp = tempfile.NamedTemporaryFile(delete=False)
    os.chmod(temp.name, 0o644)
    try:
        temp.write(json.dumps(content).encode('utf-8'))
    finally:
        temp.close()
    shutil.move(temp.name, save_path)
    #os.chmod(save_path, 0o644)

# Step1. compute multiple sequence alignment (MSA) and template features
def seq_to_msa_tf(user_id_path):
    global python_env, seq_database
    fd_path1 = os.path.join(user_id_path, "user_input")
    fd_path2 = os.path.join(user_id_path, "msa_tf_output")
    os.umask(0)
    os.mkdir(fd_path2)
    max_template_date=(datetime.now()+timedelta(days=30)).strftime('%Y-%m-%d')
    cmd = f"""
    bash -c "{python_env} {alphapulldown_path}create_individual_features.py \
    --fasta_paths={fd_path1}/baits.fasta,{fd_path1}/candidates.fasta \
    --data_dir={seq_database} \
    --save_msa_files=False \
    --output_dir={fd_path2} \
    --use_precomputed_msas=False \
    --max_template_date={max_template_date} \
    --skip_existing=True"
    """
    os.system(cmd)

# Step2. predict structures
def msa_tf_to_predict_struct(user_id_path):
    global python_env, seq_database
    fd_path1 = os.path.join(user_id_path, "user_input")
    fd_path2 = os.path.join(user_id_path, "msa_tf_output")
    fd_path3 = os.path.join(user_id_path, "predict_struct_output")
    os.umask(0)
    os.mkdir(fd_path3)
    cmd = f"""
    {python_env} {alphapulldown_path}run_multimer_jobs.py \
    --mode=pulldown \
    --num_cycle=3 \
    --num_predictions_per_model=5 \
    --output_path={fd_path3} \
    --data_dir={seq_database} \
    --protein_lists={fd_path1}/baits.txt,{fd_path1}/candidates.txt \
    --monomer_objects_dir={fd_path2}
    """
    os.system(cmd)

# Step3. evalution and visualisation by singularity
def predict_struct_get_good_pae(user_id_path):
    global run_pae_img
    fd_path1 = os.path.join(user_id_path, "predict_struct_output")
    fd_path2 = os.path.join(user_id_path, "eval_vis_output")
    os.umask(0)
    os.mkdir(fd_path2)
    cmd = f"""
    singularity exec \
    --no-home \
    --bind {fd_path1}:/mnt \
    {run_pae_img} \
    run_get_good_pae.sh \
    --output_dir=/mnt \
    --cutoff=100
    """
    os.system(cmd)
    os.system(f"mv {fd_path1}/pi_score_outputs {fd_path2}/")
    os.system(f"mv {fd_path1}/predictions_with_good_interpae.csv {fd_path2}/")

# Step4. Obtain aligned sequence files by pymol
def pymol_get_align_pdb(user_id_path):
    global pymol_script_path
    csv_path = os.path.join(user_id_path, "eval_vis_output", "predictions_with_good_interpae.csv")
    load_path = os.path.join(user_id_path, "predict_struct_output")
    save_path = os.path.join(user_id_path, "eval_vis_output", "align_pdb")
    cmd = f"""
    pymol -qrc \
    {pymol_script_path} \
    -- {csv_path} \
    {load_path} \
    {save_path}
    """
    os.system(cmd)


if __name__ == '__main__':
    cron_computing()