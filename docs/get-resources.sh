#!/bin/sh

dir=../resources/

mkdir -p $dir

curl https://source.winehq.org/git/wine.git/blob/HEAD:/fonts/tahoma.ttf -o $dir/font.ttf
curl https://upload.wikimedia.org/wikipedia/commons/thumb/f/f0/Chess_kdt45.svg/1920px-Chess_kdt45.svg.png -o $dir/bk.png
curl https://upload.wikimedia.org/wikipedia/commons/thumb/4/47/Chess_qdt45.svg/1024px-Chess_qdt45.svg.png -o $dir/bq.png
curl https://upload.wikimedia.org/wikipedia/commons/thumb/f/ff/Chess_rdt45.svg/1024px-Chess_rdt45.svg.png -o $dir/br.png
curl https://upload.wikimedia.org/wikipedia/commons/thumb/9/98/Chess_bdt45.svg/1024px-Chess_bdt45.svg.png -o $dir/bb.png
curl https://upload.wikimedia.org/wikipedia/commons/thumb/e/ef/Chess_ndt45.svg/1024px-Chess_ndt45.svg.png -o $dir/bn.png
curl https://upload.wikimedia.org/wikipedia/commons/thumb/c/c7/Chess_pdt45.svg/1024px-Chess_pdt45.svg.png -o $dir/bp.png
curl https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Chess_klt45.svg/1024px-Chess_klt45.svg.png -o $dir/wk.png
curl https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/Chess_qlt45.svg/1024px-Chess_qlt45.svg.png -o $dir/wq.png
curl https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Chess_rlt45.svg/1024px-Chess_rlt45.svg.png -o $dir/wr.png
curl https://upload.wikimedia.org/wikipedia/commons/thumb/b/b1/Chess_blt45.svg/1024px-Chess_blt45.svg.png -o $dir/wb.png
curl https://upload.wikimedia.org/wikipedia/commons/thumb/7/70/Chess_nlt45.svg/1024px-Chess_nlt45.svg.png -o $dir/wn.png
curl https://upload.wikimedia.org/wikipedia/commons/thumb/4/45/Chess_plt45.svg/1024px-Chess_plt45.svg.png -o $dir/wp.png

