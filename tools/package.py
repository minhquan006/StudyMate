"""Create a source ZIP from tracked and nonignored files; exclude credentials/runtime data."""
import argparse, hashlib, json, subprocess, zipfile
from pathlib import Path
ROOT=Path(__file__).resolve().parents[1]
NAME='52400227_NguyenMinhQuan_52400258_LeNguyenVanAn_52400252_PhamVanTu'
def files():
 data=subprocess.check_output(['git','ls-files','-z','--cached','--others','--exclude-standard'],cwd=ROOT)
 for name in sorted(set(data.decode().split('\0'))- {''}):
  p=Path(name)
  if name in ['config.php','.env'] or (p.parts[0]=='storage' and name!='storage/.gitkeep') or '__pycache__' in p.parts or p.suffix in ['.sqlite','.zip'] or (p.suffix=='.sql' and p.parts[0]!='database') or (name.startswith('.env.') and name!='.env.example'):continue
  path=ROOT/p
  if path.is_file() and not path.is_symlink():yield name,path
if __name__=='__main__':
 parser=argparse.ArgumentParser();parser.add_argument('output',type=Path);args=parser.parse_args();args.output.parent.mkdir(parents=True,exist_ok=True)
 if args.output.exists():raise SystemExit('Refusing to overwrite existing ZIP')
 entries=list(files())
 with zipfile.ZipFile(args.output,'w',zipfile.ZIP_DEFLATED) as z:
  for name,path in entries:z.write(path,NAME+'/'+name)
 with zipfile.ZipFile(args.output) as z:assert z.testzip() is None
 manifest={name:hashlib.sha256(path.read_bytes()).hexdigest() for name,path in entries}
 args.output.with_suffix('.manifest.json').write_text(json.dumps(manifest,indent=2))
 print('Created',args.output,'with',len(entries),'files; archive integrity passed.')
