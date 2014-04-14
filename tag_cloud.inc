<?php
class wordCloud
{
  private $wordsArray = array();

  /*
   * PHP 5 Constructor
   *
   * @param array $words
   * @return void
   */

  
  public function __construct($words = false) {
  
     if ($words !== false && is_array($words)) {
        foreach ($words as $key => $value) 
        {
         $this->addWord($value);
        }
     }
  }
  
  /*
   * Assign word to array
   *
   * @param string $word
   * @return string
   */
  
  public function addWord($word, $value = 1) {
  
    $word = strtolower($word);
    if (array_key_exists($word, $this->wordsArray)) 
      $this->wordsArray[$word] += $value;
    
    else 
      $this->wordsArray[$word] = $value;
    
    return $this->wordsArray[$word];
  }
  
  /*
   * Shuffle associated names in array
   */
   
   public function shuffleCloud() {
      $keys = array_keys($this->wordsArray);
      
      shuffle($keys);
      

      
      if (count($keys) && is_array($keys)) {
      
        $tmpArray = $this->wordsArray;
        $this->wordsArray = array();

      
        foreach ($keys as $key => $tag) {
           $this->wordsArray[$tag] = $tmpArray[$tag];
        }
      }
   }
   
  /*
   * Calculate size of words array
   */

   public function getCloudSize() {
       return array_sum($this->wordsArray);
   }
   
  /*
   * Return words array
   */
   
   public function getCloud() {
     return $this->wordsArray;
   }
  
  /*
   * Get the class range using a percentage
   *
   * @returns int $class
   */
   
   private function getClassFromPercent($percent) {
   
    if ($percent >= 99)
      $class = 1;
    else if ($percent >= 70)
      $class = 2;
    else if ($percent >= 60)
      $class = 3;
    else if ($percent >= 50)
      $class = 4;
    else if ($percent >= 40)
      $class = 5;
    else if ($percent >= 30)
      $class = 6;
    else if ($percent >= 20)
      $class = 7;
    else if ($percent >= 10)
      $class = 8;
    else
      $class = 0;

    return $class;
   }
   
  /*
   * Create the HTML code for each word and apply font size.
   *
   * @returns string $spans
   */
   
   
  public function showCloud()
  {
    $this->shuffleCloud();
    $this->max = max($this->wordsArray);

    if (is_array($this->wordsArray))
      {
        $return = "";
        foreach ($this->wordsArray as $word => $popularity)
          {
            $sizeRange = $this->getClassFromPercent(($popularity / $this->max) * 100);
            $return .= "<a style='text-decoration:none;' href='tag_bookmarks.php?tag=" . 
                        $word . "'><span class='word size".$sizeRange."'>  ". 
                        $word."  </span></a>";
          }
        return $return;
      }
  }
  
   public function orderList() {
      $keys = array_keys($this->wordsArray);
      
      sort($keys);
           
      if (count($keys) && is_array($keys)) {
      
        $tmpArray = $this->wordsArray;
        $this->wordsArray = array();

        foreach ($keys as $key => $tag) {
           $this->wordsArray[$tag] = $tmpArray[$tag];
        }
      }
   }
  
  //Use showList to display items in the array as a list rather than a cloud
  public function showList()
  {
    $this->orderList();

    if (is_array($this->wordsArray))
      {
        $return = "";
        foreach ($this->wordsArray as $word => $popularity)
          {
            
            $return .= "<span style='float:left;'><a class='follow' href='tag_bookmarks.php?tag=" . 
                        $word . "'>". 
                        $word." (" . $popularity . ")</a></span> ";
          }
        return $return;
      }
  }
  
}


?>