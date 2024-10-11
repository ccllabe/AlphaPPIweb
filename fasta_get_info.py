# -*- coding: utf-8 -*-

# import library
# initial tool
import os
import sys
import re
import json
# biopython
from Bio import SeqIO

# Traverse the folder to obtain the sequence info of the *.fasta file
def fd_fastas_get_info(path):
    f_restrict = [
        {"name": "baits.fasta", "max_n": 1, "max_len": 1000 },
        {"name": "candidates.fasta", "max_n": 15, "max_len": 1000 }
    ]
    check = all(obj in [f['name'] for f in f_restrict] for obj in os.listdir(path)) and (len(os.listdir(path))>1)
    if not check:
        error_output = json.dumps({"error": "Undefined data appears in the folder!!"})
        print(error_output)
        return
    for file in f_restrict:
        src_path = os.path.join(path, file["name"])
        target_path = os.path.join(path, f'{os.path.splitext(file["name"])[0]}.txt')
        check, msg = fasta_get_info(src_path, file["max_n"], file["max_len"], target_path)
        if not check:
            error_output = json.dumps({"error": f'{os.path.splitext(file["name"])[0]}: {msg}'})
            print(error_output)
            return

# Get the sequence info of the .fasta file
def fasta_get_info(src_path, max_n, max_len, target_path):
    headers = []
    with open(src_path, "r") as f:
        for record in SeqIO.parse(f, "fasta"):
            if(len(record.seq)>max_len) or (len(record.seq)<1):
                return False, f"Sequences length is greater than {max_len} or does not exist!!"
            if(not bool(re.fullmatch(r'[^\0\\/:*?"<>|]{1,50}', record.description))):
                return False, f"The sequence description is treated as a sequence name in the program, and there is an error in the sequence name format!!"
            header = f"{record.description}\n"
            if header in headers:
                return False, f"The sequence description is treated as a sequence name in the program, and the sequence name is repeated!!"
            headers.append(header)
    if(len(headers)>max_n) or (len(headers)<1):
        return False, f"The number of sequences is greater than {max_n} or does not exist!!"
    with open(target_path, "w") as f:
        f.writelines(headers)
    return True, None


if __name__ == '__main__':
    try:
        user_input_path = sys.argv[1]
        fd_fastas_get_info(user_input_path)
    except Exception as e:
        error_message = str(e)
        error_output = json.dumps({"error": error_message})
        print(error_output)
        sys.exit(1)
