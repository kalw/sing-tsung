<?php

class FPDF2 extends FPDF
{
//Private properties
var $DisplayPreferences='';
var $outlines=array();
var $OutlineRoot;
var $flowingBlockAttr;

/*******************************************************************************
*                                                                              *
*                              Public methods                                  *
*                                                                              *
*******************************************************************************/

//-------------------------FLOWING BLOCK------------------------------------//
//EDITEI some things (added/changed)                                        //
//The following functions were originally written by Damon Kohler           //
//--------------------------------------------------------------------------//

function saveFont()
{
   $saved = array();
   $saved[ 'family' ] = $this->FontFamily;
   $saved[ 'style' ] = $this->FontStyle;
   $saved[ 'sizePt' ] = $this->FontSizePt;
   $saved[ 'size' ] = $this->FontSize;
   $saved[ 'curr' ] =& $this->CurrentFont;
   $saved[ 'color' ] = $this->TextColor; //EDITEI
   $saved[ 'bgcolor' ] = $this->FillColor; //EDITEI
   $saved[ 'HREF' ] = $this->HREF; //EDITEI
   $saved[ 'underline' ] = $this->underline; //EDITEI
   $saved[ 'strike' ] = $this->strike; //EDITEI
   $saved[ 'SUP' ] = $this->SUP; //EDITEI
   $saved[ 'SUB' ] = $this->SUB; //EDITEI
   $saved[ 'linewidth' ] = $this->LineWidth; //EDITEI
   $saved[ 'drawcolor' ] = $this->DrawColor; //EDITEI
   $saved[ 'is_outline' ] = $this->outline_on; //EDITEI

   return $saved;
}

function restoreFont( $saved )
{
   $this->FontFamily = $saved[ 'family' ];
   $this->FontStyle = $saved[ 'style' ];
   $this->FontSizePt = $saved[ 'sizePt' ];
   $this->FontSize = $saved[ 'size' ];
   $this->CurrentFont =& $saved[ 'curr' ];
   $this->TextColor = $saved[ 'color' ]; //EDITEI
   $this->FillColor = $saved[ 'bgcolor' ]; //EDITEI
   $this->ColorFlag = ($this->FillColor != $this->TextColor); //Restore ColorFlag as well
   $this->HREF = $saved[ 'HREF' ]; //EDITEI
   $this->underline = $saved[ 'underline' ]; //EDITEI
   $this->strike = $saved[ 'strike' ]; //EDITEI
   $this->SUP = $saved[ 'SUP' ]; //EDITEI
   $this->SUB = $saved[ 'SUB' ]; //EDITEI
   $this->LineWidth = $saved[ 'linewidth' ]; //EDITEI
   $this->DrawColor = $saved[ 'drawcolor' ]; //EDITEI
   $this->outline_on = $saved[ 'is_outline' ]; //EDITEI

   if( $this->page > 0)
      $this->_out( sprintf( 'BT /F%d %.2f Tf ET', $this->CurrentFont[ 'i' ], $this->FontSizePt ) );
}

function newFlowingBlock( $w, $h, $b = 0, $a = 'J', $f = 0 , $is_table = false )
{
   // cell width in points
   if ($is_table)  $this->flowingBlockAttr[ 'width' ] = ($w * $this->k);
   else $this->flowingBlockAttr[ 'width' ] = ($w * $this->k) - (2*$this->cMargin*$this->k);
   // line height in user units
   $this->flowingBlockAttr[ 'is_table' ] = $is_table;
   $this->flowingBlockAttr[ 'height' ] = $h;
   $this->flowingBlockAttr[ 'lineCount' ] = 0;
   $this->flowingBlockAttr[ 'border' ] = $b;
   $this->flowingBlockAttr[ 'align' ] = $a;
   $this->flowingBlockAttr[ 'fill' ] = $f;
   $this->flowingBlockAttr[ 'font' ] = array();
   $this->flowingBlockAttr[ 'content' ] = array();
   $this->flowingBlockAttr[ 'contentWidth' ] = 0;
}

function finishFlowingBlock($outofblock=false)
{
   if (!$outofblock) $currentx = $this->x; //EDITEI - in order to make the Cell method work better
   //prints out the last chunk
   $is_table = $this->flowingBlockAttr[ 'is_table' ];
   $maxWidth =& $this->flowingBlockAttr[ 'width' ];
   $lineHeight =& $this->flowingBlockAttr[ 'height' ];
   $border =& $this->flowingBlockAttr[ 'border' ];
   $align =& $this->flowingBlockAttr[ 'align' ];
   $fill =& $this->flowingBlockAttr[ 'fill' ];
   $content =& $this->flowingBlockAttr[ 'content' ];
   $font =& $this->flowingBlockAttr[ 'font' ];
   $contentWidth =& $this->flowingBlockAttr[ 'contentWidth' ];
   $lineCount =& $this->flowingBlockAttr[ 'lineCount' ];

   // set normal spacing
   $this->_out( sprintf( '%.3f Tw', 0 ) );
   $this->ws = 0;

   // the amount of space taken up so far in user units
   $usedWidth = 0;

   // Print out each chunk
   //EDITEI - Print content according to alignment
   $empty = $maxWidth - $contentWidth;
   $empty /= $this->k;
   $b = ''; //do not use borders
   $arraysize = count($content);
   $margins = (2*$this->cMargin);
   if ($outofblock)
   {
      $align = 'C';
      $empty = 0;
      $margins = $this->cMargin;
   }
   switch($align)
   {
      case 'R':
          foreach ( $content as $k => $chunk )
          {
              $this->restoreFont( $font[ $k ] );
              $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
              // determine which borders should be used
              $b = '';
              if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
              if ( $k == count( $content ) - 1 && is_int( strpos( $border, 'R' ) ) ) $b .= 'R';

              if ($k == $arraysize-1 and !$outofblock) $skipln = 1;
              else $skipln = 0;

              if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, $skipln, $align, $fill, $this->HREF , $currentx ); //mono-style line
              elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, 0, 'R', $fill, $this->HREF );//first part
              elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, $skipln, '', $fill, $this->HREF, $currentx );//last part
              else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, $this->HREF );//middle part
          }
          break;
      case 'L':
      case 'J':
          foreach ( $content as $k => $chunk )
          {
              $this->restoreFont( $font[ $k ] );
              $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
              // determine which borders should be used
              $b = '';
              if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
              if ( $k == 0 && is_int( strpos( $border, 'L' ) ) ) $b .= 'L';

              if ($k == $arraysize-1 and !$outofblock) $skipln = 1;
              else $skipln = 0;

              if (!$is_table and !$outofblock and !$fill and $align=='L' and $k == 0) {$align='';$margins=0;} //Remove margins in this special (though often) case

              if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, $skipln, $align, $fill, $this->HREF , $currentx ); //mono-style line
              elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, $skipln, $align, $fill, $this->HREF );//first part
              elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, $skipln, '', $fill, $this->HREF, $currentx );//last part
              else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, $skipln, '', $fill, $this->HREF );//middle part
          }
          break;
      case 'C':
          foreach ( $content as $k => $chunk )
          {
              $this->restoreFont( $font[ $k ] );
              $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
              // determine which borders should be used
              $b = '';
              if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';

              if ($k == $arraysize-1 and !$outofblock) $skipln = 1;
              else $skipln = 0;

              if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, $skipln, $align, $fill, $this->HREF , $currentx ); //mono-style line
              elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, 0, 'R', $fill, $this->HREF );//first part
              elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, $skipln, 'L', $fill, $this->HREF, $currentx );//last part
              else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, $this->HREF );//middle part
          }
          break;
     default: break;
   }
}

function WriteFlowingBlock( $s , $outofblock = false )
{
    if (!$outofblock) $currentx = $this->x; //EDITEI - in order to make the Cell method work better
    $is_table = $this->flowingBlockAttr[ 'is_table' ];
    // width of all the content so far in points
    $contentWidth =& $this->flowingBlockAttr[ 'contentWidth' ];
    // cell width in points
    $maxWidth =& $this->flowingBlockAttr[ 'width' ];
    $lineCount =& $this->flowingBlockAttr[ 'lineCount' ];
    // line height in user units
    $lineHeight =& $this->flowingBlockAttr[ 'height' ];
    $border =& $this->flowingBlockAttr[ 'border' ];
    $align =& $this->flowingBlockAttr[ 'align' ];
    $fill =& $this->flowingBlockAttr[ 'fill' ];
    $content =& $this->flowingBlockAttr[ 'content' ];
    $font =& $this->flowingBlockAttr[ 'font' ];

    $font[] = $this->saveFont();
    $content[] = '';

    $currContent =& $content[ count( $content ) - 1 ];

    // where the line should be cutoff if it is to be justified
    $cutoffWidth = $contentWidth;

    // for every character in the string
    for ( $i = 0; $i < strlen( $s ); $i++ )
    {
       // extract the current character
       $c = $s{$i};
       // get the width of the character in points
       $cw = $this->CurrentFont[ 'cw' ][ $c ] * ( $this->FontSizePt / 1000 );

       if ( $c == ' ' )
       {
           $currContent .= ' ';
           $cutoffWidth = $contentWidth;
           $contentWidth += $cw;
           continue;
       }
       // try adding another char
       if ( $contentWidth + $cw > $maxWidth )
       {
           // it won't fit, output what we already have
           $lineCount++;
           //Readjust MaxSize in order to use the whole page width
           if ($outofblock and ($lineCount == 1) ) $maxWidth = $this->pgwidth * $this->k;
           // contains any content that didn't make it into this print
           $savedContent = '';
           $savedFont = array();
           // first, cut off and save any partial words at the end of the string
           $words = explode( ' ', $currContent );

           // if it looks like we didn't finish any words for this chunk
           if ( count( $words ) == 1 )
           {
              // save and crop off the content currently on the stack
              $savedContent = array_pop( $content );
              $savedFont = array_pop( $font );

              // trim any trailing spaces off the last bit of content
              $currContent =& $content[ count( $content ) - 1 ];
              $currContent = rtrim( $currContent );
           }
           else // otherwise, we need to find which bit to cut off
           {
              $lastContent = '';
              for ( $w = 0; $w < count( $words ) - 1; $w++) $lastContent .= "{$words[ $w ]} ";

              $savedContent = $words[ count( $words ) - 1 ];
              $savedFont = $this->saveFont();
              // replace the current content with the cropped version
              $currContent = rtrim( $lastContent );
           }
           // update $contentWidth and $cutoffWidth since they changed with cropping
           $contentWidth = 0;
           foreach ( $content as $k => $chunk )
           {
              $this->restoreFont( $font[ $k ] );
              $contentWidth += $this->GetStringWidth( $chunk ) * $this->k;
           }
           $cutoffWidth = $contentWidth;
           // if it's justified, we need to find the char spacing
           if( $align == 'J' )
           {
              // count how many spaces there are in the entire content string
              $numSpaces = 0;
              foreach ( $content as $chunk ) $numSpaces += substr_count( $chunk, ' ' );
              // if there's more than one space, find word spacing in points
              if ( $numSpaces > 0 ) $this->ws = ( $maxWidth - $cutoffWidth ) / $numSpaces;
              else $this->ws = 0;
              $this->_out( sprintf( '%.3f Tw', $this->ws ) );
           }
           // otherwise, we want normal spacing
           else $this->_out( sprintf( '%.3f Tw', 0 ) );

           //EDITEI - Print content according to alignment
           if (!isset($numSpaces)) $numSpaces = 0;
           $contentWidth -= ($this->ws*$numSpaces);
           $empty = $maxWidth - $contentWidth - 2*($this->ws*$numSpaces);
           $empty /= $this->k;
           $b = ''; //do not use borders
           /*'If' below used in order to fix "first-line of other page with justify on" bug*/
           if($this->y+$this->divheight>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
             {
                $bak_x=$this->x;//Current X position
                $ws=$this->ws;//Word Spacing
                  if($ws>0)
                  {
                     $this->ws=0;
                     $this->_out('0 Tw');
                  }
                  $this->AddPage($this->CurOrientation);
                  $this->x=$bak_x;
                  if($ws>0)
                  {
                     $this->ws=$ws;
                     $this->_out(sprintf('%.3f Tw',$ws));
                }
             }
           $arraysize = count($content);
           $margins = (2*$this->cMargin);
           if ($outofblock)
           {
              $align = 'C';
              $empty = 0;
              $margins = $this->cMargin;
           }
           switch($align)
           {
             case 'R':
                 foreach ( $content as $k => $chunk )
                 {
                     $this->restoreFont( $font[ $k ] );
                     $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
                     // determine which borders should be used
                     $b = '';
                     if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
                     if ( $k == count( $content ) - 1 && is_int( strpos( $border, 'R' ) ) ) $b .= 'R';

                     if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, 1, $align, $fill, $this->HREF , $currentx ); //mono-style line
                     elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, 0, 'R', $fill, $this->HREF );//first part
                     elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, 1, '', $fill, $this->HREF, $currentx );//last part
                     else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, $this->HREF );//middle part
                 }
                break;
             case 'L':
             case 'J':
                 foreach ( $content as $k => $chunk )
                 {
                     $this->restoreFont( $font[ $k ] );
                     $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
                     // determine which borders should be used
                     $b = '';
                     if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';
                     if ( $k == 0 && is_int( strpos( $border, 'L' ) ) ) $b .= 'L';

                     if (!$is_table and !$outofblock and !$fill and $align=='L' and $k == 0)
                     {
                         //Remove margins in this special (though often) case
                         $align='';
                         $margins=0;
                     }

                     if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, 1, $align, $fill, $this->HREF , $currentx ); //mono-style line
                     elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2), $lineHeight, $chunk, $b, 0, $align, $fill, $this->HREF );//first part
                     elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + $empty, $lineHeight, $chunk, $b, 1, '', $fill, $this->HREF, $currentx );//last part
                     else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, $this->HREF );//middle part

                     if (!$is_table and !$outofblock and !$fill and $align=='' and $k == 0)
                     {
                         $align = 'L';
                         $margins = (2*$this->cMargin);
                     }
                 }
                 break;
             case 'C':
                 foreach ( $content as $k => $chunk )
                 {
                     $this->restoreFont( $font[ $k ] );
                     $stringWidth = $this->GetStringWidth( $chunk ) + ( $this->ws * substr_count( $chunk, ' ' ) / $this->k );
                     // determine which borders should be used
                     $b = '';
                     if ( $lineCount == 1 && is_int( strpos( $border, 'T' ) ) ) $b .= 'T';

                     if ($arraysize == 1) $this->Cell( $stringWidth + $margins + $empty, $lineHeight, $chunk, $b, 1, $align, $fill, $this->HREF , $currentx ); //mono-style line
                     elseif ($k == 0) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, 0, 'R', $fill, $this->HREF );//first part
                     elseif ($k == $arraysize-1 ) $this->Cell( $stringWidth + ($margins/2) + ($empty/2), $lineHeight, $chunk, $b, 1, 'L', $fill, $this->HREF, $currentx );//last part
                     else $this->Cell( $stringWidth , $lineHeight, $chunk, $b, 0, '', $fill, $this->HREF );//middle part
                 }
                 break;
                 default: break;
           }
           // move on to the next line, reset variables, tack on saved content and current char
           $this->restoreFont( $savedFont );
           $font = array( $savedFont );
           $content = array( $savedContent . $s{ $i } );

           $currContent =& $content[ 0 ];
           $contentWidth = $this->GetStringWidth( $currContent ) * $this->k;
           $cutoffWidth = $contentWidth;
       }
       // another character will fit, so add it on
       else
       {
           $contentWidth += $cw;
           $currContent .= $s{ $i };
       }
    }
}
//----------------------END OF FLOWING BLOCK------------------------------------//


/*******************************************************************************
*                                                                              *
*                              Protected methods                               *
*                                                                              *
*******************************************************************************/

function _preprocessfontfamily($family)
{
    if($family=='monospace')
        $family='courier';
    if($family=='serif')
        $family='times';
    if($family=='sans')
        $family='arial';
    return $family;
}

//EDITEI - Done after reading a little about PDF reference guide
function DottedRect($x=100,$y=150,$w=50,$h=50)
{
  $x *= $this->k ;
  $y = ($this->h-$y)*$this->k;
  $w *= $this->k ;
  $h *= $this->k ;// - h?

  $herex = $x;
  $herey = $y;

  //Make fillcolor == drawcolor
  $bak_fill = $this->FillColor;
  $this->FillColor = $this->DrawColor;
  $this->FillColor = str_replace('RG','rg',$this->FillColor);
  $this->_out($this->FillColor);

  while ($herex < ($x + $w)) //draw from upper left to upper right
  {
  $this->DrawDot($herex,$herey);
  $herex += (3*$this->k);
  }
  $herex = $x + $w;
  while ($herey > ($y - $h)) //draw from upper right to lower right
  {
  $this->DrawDot($herex,$herey);
  $herey -= (3*$this->k);
  }
  $herey = $y - $h;
  while ($herex > $x) //draw from lower right to lower left
  {
  $this->DrawDot($herex,$herey);
  $herex -= (3*$this->k);
  }
  $herex = $x;
  while ($herey < $y) //draw from lower left to upper left
  {
  $this->DrawDot($herex,$herey);
  $herey += (3*$this->k);
  }
  $herey = $y;

  $this->FillColor = $bak_fill;
  $this->_out($this->FillColor); //return fillcolor back to normal
}

//EDITEI - Done after reading a little about PDF reference guide
function DrawDot($x,$y) //center x y
{
  $op = 'B'; // draw Filled Dots
  //F == fill //S == stroke //B == stroke and fill
  $r = 0.5 * $this->k;  //raio

  //Start Point
  $x1 = $x - $r;
  $y1 = $y;
  //End Point
  $x2 = $x + $r;
  $y2 = $y;
  //Auxiliar Point
  $x3 = $x;
  $y3 = $y + (2*$r);// 2*raio to make a round (not oval) shape

  //Round join and cap
  $s="\n".'1 J'."\n";
  $s.='1 j'."\n";

  //Upper circle
  $s.=sprintf('%.3f %.3f m'."\n",$x1,$y1); //x y start drawing
  $s.=sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c'."\n",$x1,$y1,$x3,$y3,$x2,$y2);//Bezier curve
  //Lower circle
  $y3 = $y - (2*$r);
  $s.=sprintf("\n".'%.3f %.3f m'."\n",$x1,$y1); //x y start drawing
  $s.=sprintf('%.3f %.3f %.3f %.3f %.3f %.3f c'."\n",$x1,$y1,$x3,$y3,$x2,$y2);
  $s.=$op."\n"; //stroke and fill

  //Draw in PDF file
  $this->_out($s);
}

function SetDash($black=false,$white=false)
{
        if($black and $white) $s=sprintf('[%.3f %.3f] 0 d',$black*$this->k,$white*$this->k);
        else $s='[] 0 d';
        $this->_out($s);
}

function Bookmark($txt,$level=0,$y=0)
{
    if($y == -1) $y = $this->GetY();
    $this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
}

function DisplayPreferences($preferences)
{
    $this->DisplayPreferences .= $preferences;
}

function _putresources()
{
    parent::_putresources();
    $this->_putbookmarks();
}

function _putbookmarks()
{
    $nb=count($this->outlines);
    if($nb==0) return;
    $lru=array();
    $level=0;
    foreach($this->outlines as $i=>$o)
    {
        if($o['l']>0)
        {
            $parent=$lru[$o['l']-1];
            //Set parent and last pointers
            $this->outlines[$i]['parent']=$parent;
            $this->outlines[$parent]['last']=$i;
            if($o['l']>$level)
            {
                //Level increasing: set first pointer
                $this->outlines[$parent]['first']=$i;
            }
        }
        else
            $this->outlines[$i]['parent']=$nb;
        if($o['l']<=$level and $i>0)
        {
            //Set prev and next pointers
            $prev=$lru[$o['l']];
            $this->outlines[$prev]['next']=$i;
            $this->outlines[$i]['prev']=$prev;
        }
        $lru[$o['l']]=$i;
        $level=$o['l'];
    }
    //Outline items
    $n=$this->n+1;
    foreach($this->outlines as $i=>$o)
    {
        $this->_newobj();
        $this->_out('<</Title '.$this->_textstring($o['t']));
        $this->_out('/Parent '.($n+$o['parent']).' 0 R');
        if(isset($o['prev']))
            $this->_out('/Prev '.($n+$o['prev']).' 0 R');
        if(isset($o['next']))
            $this->_out('/Next '.($n+$o['next']).' 0 R');
        if(isset($o['first']))
            $this->_out('/First '.($n+$o['first']).' 0 R');
        if(isset($o['last']))
            $this->_out('/Last '.($n+$o['last']).' 0 R');
        $this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]',1+2*$o['p'],($this->h-$o['y'])*$this->k));
        $this->_out('/Count 0>>');
        $this->_out('endobj');
    }
    //Outline root
    $this->_newobj();
    $this->OutlineRoot=$this->n;
    $this->_out('<</Type /Outlines /First '.$n.' 0 R');
    $this->_out('/Last '.($n+$lru[0]).' 0 R>>');
    $this->_out('endobj');
}

//EDITEI
function _putcatalog()
{
  parent::_putcatalog();
  if(count($this->outlines)>0)
  {
      $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
      $this->_out('/PageMode /UseOutlines');
  }
  if(is_int(strpos($this->DisplayPreferences,'FullScreen'))) $this->_out('/PageMode /FullScreen');
  if($this->DisplayPreferences)
  {
     $this->_out('/ViewerPreferences<<');
     if(is_int(strpos($this->DisplayPreferences,'HideMenubar'))) $this->_out('/HideMenubar true');
     if(is_int(strpos($this->DisplayPreferences,'HideToolbar'))) $this->_out('/HideToolbar true');
     if(is_int(strpos($this->DisplayPreferences,'HideWindowUI'))) $this->_out('/HideWindowUI true');
     if(is_int(strpos($this->DisplayPreferences,'DisplayDocTitle'))) $this->_out('/DisplayDocTitle true');
     if(is_int(strpos($this->DisplayPreferences,'CenterWindow'))) $this->_out('/CenterWindow true');
     if(is_int(strpos($this->DisplayPreferences,'FitWindow'))) $this->_out('/FitWindow true');
     $this->_out('>>');
  }
}

function _parsegif($file) //EDITEI - GIF support is now included
{
    //Function by Jérôme Fenal
    require_once(RELATIVE_PATH.'gif.php'); //GIF class in pure PHP from Yamasoft (http://www.yamasoft.com/php-gif.zip)

    $h=0;
    $w=0;
    $gif=new CGIF();

    if (!$gif->loadFile($file, 0))
        $this->Error("GIF parser: unable to open file $file");

    if($gif->m_img->m_gih->m_bLocalClr) {
        $nColors = $gif->m_img->m_gih->m_nTableSize;
        $pal = $gif->m_img->m_gih->m_colorTable->toString();
        if($bgColor != -1) {
            $bgColor = $this->m_img->m_gih->m_colorTable->colorIndex($bgColor);
        }
        $colspace='Indexed';
    } elseif($gif->m_gfh->m_bGlobalClr) {
        $nColors = $gif->m_gfh->m_nTableSize;
        $pal = $gif->m_gfh->m_colorTable->toString();
        if((isset($bgColor)) and $bgColor != -1) {
            $bgColor = $gif->m_gfh->m_colorTable->colorIndex($bgColor);
        }
        $colspace='Indexed';
    } else {
        $nColors = 0;
        $bgColor = -1;
        $colspace='DeviceGray';
        $pal='';
    }

    $trns='';
    if($gif->m_img->m_bTrans && ($nColors > 0)) {
        $trns=array($gif->m_img->m_nTrans);
    }

    $data=$gif->m_img->m_data;
    $w=$gif->m_gfh->m_nWidth;
    $h=$gif->m_gfh->m_nHeight;

    if($colspace=='Indexed' and empty($pal))
        $this->Error('Missing palette in '.$file);

    if ($this->compress) {
        $data=gzcompress($data);
        return array( 'w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>8, 'f'=>'FlateDecode', 'pal'=>$pal, 'trns'=>$trns, 'data'=>$data);
    } else {
        return array( 'w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>8, 'pal'=>$pal, 'trns'=>$trns, 'data'=>$data);
    }
}

}


