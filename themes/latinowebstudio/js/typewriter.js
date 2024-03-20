// alert('hello');
const TypeWriter = function(txtElement, words, wait = 1000) {
    this.txtElement = txtElement;
    this.words = words;
    this.txt = '';
    this.wordIndex = 0;
    this.wait = parseInt(wait, 10);
    this.type();
    this.isDeleting = false;
  }
  
  // Type Method
  TypeWriter.prototype.type = function() {
    // console.log('hello');
    // current index of word
    const current = this.wordIndex % this.words.length;
    
    // get full text of current word
    const fullTxt = this.words[current]
    
  // check if deleting
    if(this.isDeleting){
      // remove char
      this.txt = fullTxt.substring(0, this.txt.length - 1);
    } else {
      // add char
      this.txt = fullTxt.substring(0, this.txt.length + 1);
    }
    
    // insert txt into element
    this.txtElement.innerHTML = `<span class="txt">${this.txt}</span>`;
    
    //Type Speed
    let typeSpeed = 100;
    
    if(this.isDeleting){
      typeSpeed /= 2;
    }
    
    // if word is complete
    if(!this.isDeleting && this.txt === fullTxt){
      // make pause at end
      typeSpeed = this.wait;
      // set delete to true
      this.isDeleting = true;
    } else if(this.isDeleting && this.txt === ''){
      this.isDeleting = false;
      // move to next word
      this.wordIndex++;
      // pause before start typing
      typeSpeed = 200;
    }
    
    setTimeout( () => this.type(), typeSpeed )
  }
  
  // init on dom load
  
  window.addEventListener('load', init);
  // Init App
  function init(){
    const txtElement = document.querySelector('.txt-type');
    const words = JSON.parse(txtElement.getAttribute('data-words'));
    const wait = txtElement.getAttribute('data-wait');
    // Init Typewriter
    new TypeWriter(txtElement, words, wait);
  }