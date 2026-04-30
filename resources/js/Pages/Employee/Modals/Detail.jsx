import { Modal } from 'antd'
import React, { useEffect, useState } from 'react'

export default function Detail(props) {
  const [open, setOpen] = useState(false)
  const [loading, setLoading] = useState(false)
  useEffect(()=>{
    if(props.open){
        setOpen(true)
    }
  }, [props])
  return (
    <div>
        <Modal open={open}>
            
        </Modal>
    </div>
  )
}
