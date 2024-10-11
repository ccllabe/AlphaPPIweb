# -*- coding: utf-8 -*-

# initial tool
import os
import sys
import csv
# pymol
from pymol import cmd


# get pdb information
def csv_get_pdb_info(csv_path):
    pdb_infos = []
    with open(csv_path, "r") as f:
        rows = csv.DictReader(f)
        pdb_infos = [{key: row[key] for key in ["jobs", "pdb", "interface"]} for row in rows]
    return pdb_infos

# align pdb file
def align_pdbs(load_path, save_path, pdb_infos):
    # load pdb
    for pdb_info in pdb_infos:
        pdb_info["pdb"] = pdb_info["pdb"] if pdb_info["pdb"] != "None" else "ranked_0"
        pdb_path = os.path.join(load_path, pdb_info["jobs"], f'{pdb_info["pdb"]}.pdb')
        cmd.load(pdb_path, pdb_info["jobs"])
    # align pdb chain[-1]
    target_obj = pdb_infos[0]["jobs"]
    pdb_infos[0]["interface"] = pdb_infos[0]["interface"] if pdb_infos[0]["interface"] != "None" else "C_B"
    target_chain = pdb_infos[0]["interface"].split('_')[-1]
    cmd.select(f"{target_obj}_chain", f"{target_obj} and chain {target_chain}")
    for pdb_info in pdb_infos[1:]:
        sel_obj = pdb_info["jobs"]
        pdb_info["interface"] = pdb_info["interface"] if pdb_info["interface"] != "None" else "C_B"
        sel_chain = pdb_info["interface"].split('_')[-1]
        cmd.select(f"{sel_obj}_chain", f"{sel_obj} and chain {sel_chain}")
        cmd.align(f"{sel_obj}_chain", f"{target_obj}_chain")
    # save pdb
    for pdb_info in pdb_infos:
        pdb_path = os.path.join(save_path, f'{pdb_info["jobs"]}.pdb')
        cmd.save(pdb_path, pdb_info["jobs"])


if __name__ == '__main__':
    #ex. /path/to/eval_vis_output/predictions_with_good_interpae.csv
    csv_path = sys.argv[1]
    #ex. /path/to/predict_struct_output
    load_path = sys.argv[2]
    #ex. /path/to/eval_vis_output/align_pdb
    save_path = sys.argv[3]
    os.umask(0)
    os.mkdir(save_path)
    pdb_infos = csv_get_pdb_info(csv_path)
    if len(pdb_infos)>1:
        align_pdbs(load_path, save_path, pdb_infos)
    else:
        path1 = os.path.join(load_path, pdb_infos[0]["jobs"], f'{pdb_infos[0]["pdb"]}.pdb')
        path2 = os.path.join(save_path, f'{pdb_infos[0]["jobs"]}.pdb')
        os.system(f"cp {path1} {path2}")
        