#!/usr/bin/env python3
"""Package the current source for review without runtime config or credentials."""
import argparse, hashlib, json, subprocess, zipfile
from pathlib import Path

ROOT=Path(__file__).resolve().parents[1]
FOLDER='52400227_NguyenMinhQuan_52400258_LeNguyenVanAn_52400252_PhamVanTu'
TOP=['README.md','Readme.txt','config.example.php','.env.example','Dockerfile','compose.yaml','compose.qa.yaml','.dockerignore','.gitignore','.gitattributes']
DIRS=['app','database','docker','public','tests','docs','tools']

def package(output):
    output=output.resolve()
    if output==ROOT or ROOT in output.parents:
        raise ValueError('Output must be outside checkout to avoid recursive packaging')
    output.mkdir(parents=True,exist_ok=True)
    files=[ROOT/name for name in TOP if (ROOT/name).is_file()]
    for name in DIRS:
        files.extend(p for p in (ROOT/name).rglob('*') if p.is_file() and not p.is_symlink()
                     and '__pycache__' not in p.parts and p.suffix not in ['.pyc','.zip','.sqlite','.db']
                     and p.name!='.DS_Store' and not p.name.startswith('._') and '.AppleDouble' not in p.parts
                     and p.name!='.env' and (p.name!='config.php' or p.parent==ROOT/'docker') and not (p.name.startswith('.env.') and not p.name.endswith('.example')))
    files.extend([ROOT/'storage/.gitkeep'])
    files=sorted(set(files))
    manifest={'base_commit':subprocess.check_output(['git','rev-parse','HEAD'],cwd=ROOT,text=True).strip(),
              'integration_base_commit':'81dc740916360c4f6229cd1026837939bd44fd10',
              'working_tree_changes':bool(subprocess.check_output(['git','--no-optional-locks','status','--porcelain'],cwd=ROOT,text=True)),
              'status':'Review candidate; not a signed-off final submission; see docs/QUAN_ACCEPTANCE.md',
              'files':{p.relative_to(ROOT).as_posix():hashlib.sha256(p.read_bytes()).hexdigest() for p in files}}
    archive=output/(FOLDER+'.zip')
    with zipfile.ZipFile(archive,'x',zipfile.ZIP_DEFLATED) as zipped:
        for p in files:
            zipped.write(p,FOLDER+'/'+p.relative_to(ROOT).as_posix())
        zipped.writestr(FOLDER+'/RELEASE_MANIFEST.json',json.dumps(manifest,ensure_ascii=False,indent=2)+'\n')
    # Verify every archived member hash and CRC, not just file existence.
    with zipfile.ZipFile(archive) as zipped:
        assert zipped.testzip() is None
        for name,digest in manifest['files'].items():
            assert hashlib.sha256(zipped.read(FOLDER+'/'+name)).hexdigest()==digest
        assert not any('/.git/' in n or n.endswith('/config.php') and '/docker/' not in n or '/storage/' in n and not n.endswith('/.gitkeep') for n in zipped.namelist())
    digest=hashlib.sha256(archive.read_bytes()).hexdigest()
    (output/(archive.name+'.sha256')).write_text(digest+'  '+archive.name+'\n')
    print(f'Created and verified {archive} ({len(files)} source files)')
    print('Review candidate: peer review, final Git SHA, confirmed submission requirements and team acceptance remain pending.')

if __name__=='__main__':
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output',type=Path,required=True)
    package(parser.parse_args().output)
