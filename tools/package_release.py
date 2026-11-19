#!/usr/bin/env python3
"""Package the current source for review without runtime config or credentials."""
import argparse, hashlib, json, subprocess, zipfile
from pathlib import Path

ROOT=Path(__file__).resolve().parents[1]
FOLDER='52400227_NguyenMinhQuan_52400258_LeNguyenVanAn_52400252_PhamVanTu'
TOP=['README.md','Readme.txt','config.example.php','.env.example','Dockerfile','compose.yaml','compose.qa.yaml','.dockerignore','.gitignore','.gitattributes']
DIRS=['app','database','docker','public','tests','docs','tools']

def source_files():
    """Read tracked regular files; local dumps and private files never enter ZIPs."""
    entries=subprocess.check_output(['git','ls-files','--stage','-z'],cwd=ROOT)
    files=[]
    for entry in entries.split(b'\0'):
        if not entry:
            continue
        metadata,raw_name=entry.split(b'\t',1)
        mode,_,stage=metadata.split()
        if stage!=b'0':
            raise ValueError('Resolve Git conflicts before packaging')
        name=raw_name.decode('utf-8')
        path=Path(name)
        if not (name in TOP or path.parts[0] in DIRS or name=='storage/.gitkeep'):
            continue
        lower=tuple(part.lower() for part in path.parts)
        suffixes={suffix.lower() for suffix in path.suffixes}
        private=(path.name.lower().startswith('.env') and name!='.env.example') or (
            path.name.lower()=='config.php' and name!='docker/config.php')
        excluded=any(part in {'__pycache__','.git','.appledouble','backups','backup','dumps','sessions','secrets'}
                     or part=='.ds_store' or part.startswith('._') for part in lower)
        excluded=excluded or bool(suffixes & {'.pyc','.zip','.sqlite','.sqlite3','.db','.dump','.bak','.key','.pem','.p12','.pfx'})
        excluded=excluded or ('.sql' in suffixes and name!='database/schema.mysql.sql')
        file=ROOT/path
        if mode not in {b'100644',b'100755'} or private or excluded:
            continue
        if any((ROOT/Path(*path.parts[:i])).is_symlink() for i in range(1,len(path.parts)+1)):
            continue
        if not file.is_file():
            raise ValueError('Tracked source file is missing: '+name)
        files.append(file)
    return sorted(files)

def package(output):
    output=output.resolve()
    if output==ROOT or ROOT in output.parents:
        raise ValueError('Output must be outside checkout to avoid recursive packaging')
    output.mkdir(parents=True,exist_ok=True)
    files=source_files()
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
